<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SocialPost;
use App\Models\SportsCompetition;
use RuntimeException;
use Throwable;

/**
 * Récupère les matchs terminés des compétitions suivies (Sofascore, via
 * RapidAPI) et propose des brouillons de résumés — mêmes garde-fous que
 * SocialContentGeneratorService : jamais de publication automatique, l'IA
 * met en mots un score réel, elle ne l'invente jamais.
 *
 * Authentification : SOFASCORE_API_KEY (.env), abonnement gratuit sur
 * RapidAPI à l'API « Sofascore » (éditeur Api Dojo).
 *
 * ⚠️ Les noms de champs de la réponse (events[], tournament.uniqueTournament.id,
 * homeTeam/awayTeam, homeScore/awayScore, status.type…) correspondent à la
 * structure généralement documentée pour cette API au moment de l'écriture
 * de ce fichier, mais n'ont pas pu être vérifiés contre un appel réel.
 * À confirmer/ajuster avec un exemple de réponse (onglet « Results » de
 * RapidAPI) une fois SOFASCORE_API_KEY configurée — voir matchesForDate()
 * et isFinished()/matchContext() ci-dessous si les résumés ne se génèrent
 * pas comme attendu. L'endpoint des buteurs (fetchIncidents) est une
 * hypothèse encore moins certaine que matchesForDate() : en cas d'échec ou
 * de format inattendu, elle dégrade proprement (aucun buteur détaillé) sans
 * empêcher la génération de l'article — voir MatchReportGeneratorService.
 */
final class SportsResultsService
{
    private const API_HOST = 'sofascore.p.rapidapi.com';
    private const API_URL = 'https://sofascore.p.rapidapi.com/matches/get-scheduled-events';
    private const INCIDENTS_URL = 'https://sofascore.p.rapidapi.com/matches/get-incidents';

    private const PLATFORMS = ['facebook', 'instagram', 'linkedin'];

    private string $apiKey;
    private SportsCompetition $competitions;
    private SocialPost $posts;
    private AiContentService $ai;
    private MatchReportGeneratorService $articleGenerator;

    public function __construct(?AiContentService $ai = null)
    {
        $this->apiKey = (string) ($_ENV['SOFASCORE_API_KEY'] ?? getenv('SOFASCORE_API_KEY') ?: '');
        $this->competitions = new SportsCompetition();
        $this->posts = new SocialPost();
        $this->ai = $ai ?? new AiContentService();
        $this->articleGenerator = new MatchReportGeneratorService($this->ai);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @return array{competitions:int,matches:int,created:int,articles:int,errors:int}
     */
    public function checkFinishedMatches(): array
    {
        $stats = ['competitions' => 0, 'matches' => 0, 'created' => 0, 'articles' => 0, 'errors' => 0];

        if (!$this->isConfigured()) {
            return $stats;
        }

        $competitions = $this->competitions->active();
        $stats['competitions'] = count($competitions);

        if ($competitions === []) {
            return $stats;
        }

        // Sofascore expose les événements par date (pas par plage de dates) :
        // on interroge chaque jour de la fenêtre de rattrapage, puis on filtre
        // par compétition suivie. Fenêtre courte par défaut (voir sinceDate).
        $days = $this->datesSince($this->earliestLastChecked($competitions));

        $eventsByDay = [];
        foreach ($days as $day) {
            try {
                $eventsByDay[$day] = $this->matchesForDate($day);
            } catch (Throwable $e) {
                error_log('[SportsResultsService] ' . $e->getMessage());
                $stats['errors']++;
                $eventsByDay[$day] = [];
            }
        }

        foreach ($competitions as $competition) {
            $competitionId = (string) $competition['external_competition_id'];

            foreach ($eventsByDay as $events) {
                foreach ($events as $event) {
                    $tournamentId = (string) ($event['tournament']['uniqueTournament']['id'] ?? '');
                    if ($tournamentId !== $competitionId || !$this->isFinished($event)) {
                        continue;
                    }

                    $stats['matches']++;
                    $eventId = (int) ($event['id'] ?? 0);
                    if ($eventId === 0) {
                        continue;
                    }

                    // Capturé avant la boucle ci-dessous : une fois le premier post créé,
                    // alreadyProposed() renverrait déjà "vrai" pour ce match.
                    $isNewMatch = !$this->posts->alreadyProposed('match_resume', $eventId, 'facebook');

                    foreach (self::PLATFORMS as $platform) {
                        if ($this->posts->alreadyProposed('match_resume', $eventId, $platform)) {
                            continue;
                        }

                        try {
                            $text = $this->ai->draft(
                                $this->systemPromptFor($platform),
                                $this->matchContext($event, (string) $competition['name'])
                            );

                            $this->posts->create([
                                'platform'     => $platform,
                                'status'       => 'brouillon',
                                'content_text' => $text,
                                'source_type'  => 'match_resume',
                                'source_id'    => $eventId,
                                'created_by'   => 'ia',
                            ]);

                            $stats['created']++;
                        } catch (Throwable $e) {
                            error_log('[SportsResultsService] ' . $e->getMessage());
                            $stats['errors']++;
                        }
                    }

                    if ($isNewMatch) {
                        try {
                            $this->generateMatchArticle($event, $competition);
                            $stats['articles']++;
                        } catch (Throwable $e) {
                            error_log('[SportsResultsService] ' . $e->getMessage());
                            $stats['errors']++;
                        }
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
    private function matchesForDate(string $date): array
    {
        $url = self::API_URL . '?' . http_build_query(['date' => $date]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => [
                'x-rapidapi-key: ' . $this->apiKey,
                'x-rapidapi-host: ' . self::API_HOST,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Erreur réseau cURL (Sofascore) : ' . $curlError);
        }

        $decoded = json_decode((string) $response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Réponse Sofascore invalide (HTTP %d).', $httpCode));
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $errorMsg = (string) ($decoded['message'] ?? "Erreur HTTP $httpCode");
            throw new RuntimeException('Sofascore — ' . $errorMsg);
        }

        return is_array($decoded['events'] ?? null) ? $decoded['events'] : [];
    }

    /**
     * @param array<string,mixed> $event
     * @param array<string,mixed> $competition
     */
    private function generateMatchArticle(array $event, array $competition): void
    {
        $eventId = (int) ($event['id'] ?? 0);
        $home = (string) ($event['homeTeam']['name'] ?? '?');
        $away = (string) ($event['awayTeam']['name'] ?? '?');
        $homeScore = (int) ($event['homeScore']['current'] ?? 0);
        $awayScore = (int) ($event['awayScore']['current'] ?? 0);
        $round = trim((string) ($event['roundInfo']['name'] ?? $event['tournament']['round'] ?? ''));

        $matchDate = '';
        $timestamp = $event['startTimestamp'] ?? null;
        if (is_int($timestamp)) {
            $matchDate = (new \DateTimeImmutable('@' . $timestamp))->format('d/m/Y');
        }

        $goalEvents = $this->fetchIncidents($eventId);

        $this->articleGenerator->generateArticle(
            $home,
            $away,
            $homeScore,
            $awayScore,
            (string) $competition['name'],
            $round,
            $matchDate,
            $goalEvents
        );
    }

    /**
     * Récupère les buts (buteur + minute) d'un match, si disponibles.
     * Dégrade toujours en tableau vide en cas d'échec — l'article se génère
     * quand même, juste sans détail de buteurs.
     *
     * @return array<int,array{team:string,scorer:string,minute:int}>
     */
    private function fetchIncidents(int $eventId): array
    {
        try {
            $url = self::INCIDENTS_URL . '?' . http_build_query(['matchId' => $eventId]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER     => [
                    'x-rapidapi-key: ' . $this->apiKey,
                    'x-rapidapi-host: ' . self::API_HOST,
                ],
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode < 200 || $httpCode >= 300) {
                return [];
            }

            $decoded = json_decode((string) $response, true);
            $incidents = is_array($decoded['incidents'] ?? null) ? $decoded['incidents'] : [];

            $goals = [];
            foreach ($incidents as $incident) {
                $type = strtolower((string) ($incident['incidentType'] ?? ''));
                if ($type !== 'goal') {
                    continue;
                }

                $scorer = trim((string) ($incident['player']['name'] ?? ''));
                $minute = (int) ($incident['time'] ?? 0);
                if ($scorer === '' || $minute === 0) {
                    continue;
                }

                $goals[] = [
                    'team'   => !empty($incident['isHome']) ? 'home' : 'away',
                    'scorer' => $scorer,
                    'minute' => $minute,
                ];
            }

            return $goals;
        } catch (Throwable $e) {
            error_log('[SportsResultsService] fetchIncidents: ' . $e->getMessage());

            return [];
        }
    }

    private function isFinished(array $event): bool
    {
        $status = strtolower((string) ($event['status']['type'] ?? ''));

        return $status === 'finished';
    }

    /**
     * @param array<int,array<string,mixed>> $competitions
     */
    private function earliestLastChecked(array $competitions): ?string
    {
        $dates = array_filter(array_column($competitions, 'last_checked_at'));

        return $dates === [] ? null : min($dates);
    }

    /**
     * @return array<int,string> dates (Y-m-d) de la veille de $lastCheckedAt à aujourd'hui, plafonné à 3 jours.
     */
    private function datesSince(?string $lastCheckedAt): array
    {
        $daysBack = 1;

        if ($lastCheckedAt !== null && $lastCheckedAt !== '') {
            try {
                $since = new \DateTimeImmutable($lastCheckedAt);
                $daysBack = min(3, max(1, (new \DateTimeImmutable('today'))->diff($since)->days + 1));
            } catch (Throwable) {
                $daysBack = 3;
            }
        } else {
            // Jamais vérifiée : ne remonte que les 3 derniers jours pour
            // éviter de rattraper toute une saison au premier passage.
            $daysBack = 3;
        }

        $dates = [];
        for ($i = $daysBack - 1; $i >= 0; $i--) {
            $dates[] = (new \DateTimeImmutable("-{$i} days"))->format('Y-m-d');
        }

        return $dates;
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
     * @param array<string,mixed> $event
     */
    private function matchContext(array $event, string $competitionName): string
    {
        $home = (string) ($event['homeTeam']['name'] ?? '?');
        $away = (string) ($event['awayTeam']['name'] ?? '?');
        $goalsHome = $event['homeScore']['current'] ?? '?';
        $goalsAway = $event['awayScore']['current'] ?? '?';

        $lines = [
            'Résultat de match à résumer :',
            'Compétition : ' . $competitionName,
            "Score final : {$home} {$goalsHome} - {$goalsAway} {$away}",
        ];

        return implode("\n", $lines);
    }
}
