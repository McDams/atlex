<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SocialPost;
use App\Models\SportsCompetition;
use RuntimeException;
use Throwable;

/**
 * Récupère les matchs terminés des compétitions suivies (API-Football, via
 * RapidAPI) et propose des brouillons de résumés — mêmes garde-fous que
 * SocialContentGeneratorService : jamais de publication automatique, l'IA
 * met en mots un score réel, elle ne l'invente jamais.
 *
 * Authentification : API_FOOTBALL_KEY (.env), compte gratuit sur
 * https://rapidapi.com/api-sports/api/api-football (plan gratuit limité,
 * voir GUIDE_DEPLOIEMENT.md).
 */
final class SportsResultsService
{
    private const API_HOST = 'api-football-v1.p.rapidapi.com';
    private const API_URL = 'https://api-football-v1.p.rapidapi.com/v3/fixtures';

    private const PLATFORMS = ['facebook', 'instagram', 'linkedin'];

    private string $apiKey;
    private SportsCompetition $competitions;
    private SocialPost $posts;
    private AiContentService $ai;

    public function __construct(?AiContentService $ai = null)
    {
        $this->apiKey = (string) ($_ENV['API_FOOTBALL_KEY'] ?? getenv('API_FOOTBALL_KEY') ?: '');
        $this->competitions = new SportsCompetition();
        $this->posts = new SocialPost();
        $this->ai = $ai ?? new AiContentService();
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @return array{competitions:int,matches:int,created:int,errors:int}
     */
    public function checkFinishedMatches(): array
    {
        $stats = ['competitions' => 0, 'matches' => 0, 'created' => 0, 'errors' => 0];

        if (!$this->isConfigured()) {
            return $stats;
        }

        $competitions = $this->competitions->active();
        $stats['competitions'] = count($competitions);

        foreach ($competitions as $competition) {
            $from = $this->sinceDate($competition['last_checked_at'] ?? null);

            try {
                $matches = $this->fetchFinishedMatches((string) $competition['external_competition_id'], $from);
            } catch (Throwable $e) {
                error_log('[SportsResultsService] ' . $e->getMessage());
                $stats['errors']++;
                continue;
            }

            foreach ($matches as $match) {
                $stats['matches']++;
                $fixtureId = (int) ($match['fixture']['id'] ?? 0);
                if ($fixtureId === 0) {
                    continue;
                }

                foreach (self::PLATFORMS as $platform) {
                    if ($this->posts->alreadyProposed('match_resume', $fixtureId, $platform)) {
                        continue;
                    }

                    try {
                        $text = $this->ai->draft(
                            $this->systemPromptFor($platform),
                            $this->matchContext($match, (string) $competition['name'])
                        );

                        $this->posts->create([
                            'platform'     => $platform,
                            'status'       => 'brouillon',
                            'content_text' => $text,
                            'source_type'  => 'match_resume',
                            'source_id'    => $fixtureId,
                            'created_by'   => 'ia',
                        ]);

                        $stats['created']++;
                    } catch (Throwable $e) {
                        error_log('[SportsResultsService] ' . $e->getMessage());
                        $stats['errors']++;
                    }
                }
            }

            $this->competitions->touchChecked((int) $competition['id']);
        }

        return $stats;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function fetchFinishedMatches(string $leagueId, string $from): array
    {
        // NB : le paramètre "season" d'API-Football attend l'année de début
        // de saison (ex: 2025 pour une saison 2025-26 européenne). Pour une
        // compétition à cheval sur deux années civiles, ajuster ici si les
        // résultats semblent incomplets en fin d'année.
        $season = (int) date('Y');
        $to = date('Y-m-d');

        $url = self::API_URL . '?' . http_build_query([
            'league' => $leagueId,
            'season' => $season,
            'from'   => $from,
            'to'     => $to,
            'status' => 'FT',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => [
                'X-RapidAPI-Key: ' . $this->apiKey,
                'X-RapidAPI-Host: ' . self::API_HOST,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (API-Football) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Réponse API-Football invalide (HTTP %d).', $httpCode));
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = is_array($decoded['message'] ?? null)
                ? implode(', ', $decoded['message'])
                : (string) ($decoded['message'] ?? "Erreur HTTP $httpCode");
            throw new RuntimeException('API-Football — ' . $errorMsg);
        }

        return is_array($decoded['response'] ?? null) ? $decoded['response'] : [];
    }

    private function sinceDate(?string $lastCheckedAt): string
    {
        if ($lastCheckedAt !== null && $lastCheckedAt !== '') {
            try {
                return (new \DateTimeImmutable($lastCheckedAt))->format('Y-m-d');
            } catch (Throwable) {
                // valeur invalide -> repli sur la fenêtre par défaut ci-dessous
            }
        }

        // Jamais vérifiée : ne remonte que les 3 derniers jours pour éviter
        // de rattraper toute une saison au premier passage.
        return (new \DateTimeImmutable('-3 days'))->format('Y-m-d');
    }

    private function systemPromptFor(string $platform): string
    {
        $base = 'Tu rédiges des publications pour les réseaux sociaux de ATLEX - Sport, '
            . "une association sportive à Cotonou (Bénin), à propos d'un résultat de match de "
            . "grande compétition (pas un match d'ATLEX). Ton de supporter, enthousiaste mais factuel. "
            . "N'invente ni score, ni détail : utilise uniquement les informations fournies ci-dessous. "
            . 'Donne uniquement le texte du post, sans titre ni guillemets englobants.';

        return match ($platform) {
            'instagram' => $base . ' Format Instagram : très court (1 à 3 phrases), punchy, 3 à 5 hashtags (équipes/compétition).',
            'linkedin'  => $base . ' Format LinkedIn : ton plus sobre, 2 à 3 phrases, quasi pas d\'emoji.',
            default     => $base . ' Format Facebook : 2 à 4 phrases, 1 à 2 emojis maximum.',
        };
    }

    /**
     * @param array<string,mixed> $match
     */
    private function matchContext(array $match, string $competitionName): string
    {
        $home = (string) ($match['teams']['home']['name'] ?? '?');
        $away = (string) ($match['teams']['away']['name'] ?? '?');
        $goalsHome = $match['goals']['home'] ?? '?';
        $goalsAway = $match['goals']['away'] ?? '?';
        $round = trim((string) ($match['league']['round'] ?? ''));

        $lines = [
            'Résultat de match à résumer :',
            'Compétition : ' . $competitionName . ($round !== '' ? ' — ' . $round : ''),
            "Score final : {$home} {$goalsHome} - {$goalsAway} {$away}",
        ];

        return implode("\n", $lines);
    }
}
