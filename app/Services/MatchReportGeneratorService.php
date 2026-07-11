<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HtmlSanitizer;
use App\Models\NewsArticle;
use RuntimeException;

/**
 * Rédige un article de match détaillé (rubrique Actualités) à partir du
 * score final et, si disponibles, des buteurs — jamais de fait inventé.
 * L'image de couverture est une carte de score générée (MatchCardImageGenerator),
 * jamais une photo tierce, pour éviter tout problème de droits d'auteur.
 *
 * Crée directement une ligne news_articles en brouillon (is_published = 0) :
 * la relecture, l'édition et la publication restent des actions manuelles,
 * exactement comme pour un article créé à la main.
 */
final class MatchReportGeneratorService
{
    private NewsArticle $news;
    private AiContentService $ai;
    private MatchCardImageGenerator $imageGenerator;

    public function __construct(?AiContentService $ai = null)
    {
        $this->news = new NewsArticle();
        $this->ai = $ai ?? new AiContentService();
        $this->imageGenerator = new MatchCardImageGenerator();
    }

    public function isConfigured(): bool
    {
        return $this->ai->isConfigured();
    }

    /**
     * @param array<int,array{team:string,scorer:string,minute:int}> $goalEvents
     * @return int identifiant de l'article brouillon créé
     */
    public function generateArticle(
        string $homeTeam,
        string $awayTeam,
        int $homeScore,
        int $awayScore,
        string $competitionName,
        string $roundLabel,
        string $matchDate,
        array $goalEvents = []
    ): int {
        $facts = $this->matchFacts($homeTeam, $awayTeam, $homeScore, $awayScore, $competitionName, $roundLabel, $matchDate, $goalEvents);

        $raw = $this->ai->draft($this->systemPrompt(), $facts, 1400);
        $parsed = $this->parseResponse($raw);

        $svg = $this->imageGenerator->generate(
            $homeTeam,
            $awayTeam,
            $homeScore,
            $awayScore,
            $competitionName,
            $matchDate,
            $goalEvents
        );
        $coverImage = $this->imageGenerator->save($svg, $homeTeam . '-' . $awayTeam);

        return $this->news->create([
            'title'        => $parsed['title'],
            'slug'         => slugify($parsed['title']),
            'excerpt'      => $parsed['excerpt'],
            'content'      => $parsed['content'],
            'category'     => $competitionName === 'Coupe du Monde' ? 'coupe du monde' : 'general',
            'cover_image'  => $coverImage,
            'is_published' => 0,
        ]);
    }

    private function systemPrompt(): string
    {
        return "Tu rédiges un article de match détaillé pour le site web de ATLEX - Sport "
            . "(association sportive à Cotonou, Bénin), dans la rubrique actualités. "
            . 'Style journalistique sportif, engageant, en français : une introduction qui pose '
            . "le résultat et l'enjeu du match, puis 3 à 5 paragraphes courts qui racontent le "
            . "match de façon chronologique (ouverture du score, réactions, second acte, fin de match).\n\n"
            . "RÈGLES STRICTES SUR LES FAITS :\n"
            . "- N'invente JAMAIS un score, un buteur ou une minute qui n'est pas donné explicitement ci-dessous.\n"
            . "- Si aucun but n'est fourni pour une équipe, ne mentionne aucun buteur pour cette équipe.\n"
            . "- Tu peux ajouter une ambiance générale de match (intensité, enjeu, style de jeu) sans jamais "
            . "la présenter comme un fait précis vérifié — reste dans le registre du commentaire sportif "
            . "général, jamais de statistique, blessure ou citation inventée.\n\n"
            . "Réponds STRICTEMENT selon ce format, sans rien ajouter avant ni après :\n"
            . "TITRE: <ex: \"Nom équipe A - Nom équipe B\">\n"
            . "EXTRAIT: <une phrase résumant le résultat et le contexte>\n"
            . "CONTENU:\n<le corps de l'article en HTML — uniquement <p>, <h2>, <strong>, <em>, un paragraphe par idée>";
    }

    /**
     * @param array<int,array{team:string,scorer:string,minute:int}> $goalEvents
     */
    private function matchFacts(
        string $homeTeam,
        string $awayTeam,
        int $homeScore,
        int $awayScore,
        string $competitionName,
        string $roundLabel,
        string $matchDate,
        array $goalEvents
    ): string {
        $lines = [
            "Match : {$homeTeam} vs {$awayTeam}",
            'Compétition : ' . $competitionName . ($roundLabel !== '' ? " — {$roundLabel}" : ''),
            "Date : {$matchDate}",
            "Score final : {$homeTeam} {$homeScore} - {$awayScore} {$awayTeam}",
        ];

        if ($goalEvents !== []) {
            $lines[] = 'Buts :';
            foreach ($goalEvents as $goal) {
                $team = $goal['team'] === 'home' ? $homeTeam : $awayTeam;
                $lines[] = "- {$goal['scorer']} ({$team}) à la {$goal['minute']}e minute";
            }
        } else {
            $lines[] = 'Aucun détail de but disponible — ne mentionner aucun buteur ni minute précise.';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array{title:string,excerpt:string,content:string}
     */
    private function parseResponse(string $raw): array
    {
        $title = '';
        $excerpt = '';
        $content = '';

        if (preg_match('/TITRE:\s*(.+)/i', $raw, $m) === 1) {
            $title = trim($m[1]);
        }
        if (preg_match('/EXTRAIT:\s*(.+)/i', $raw, $m) === 1) {
            $excerpt = trim($m[1]);
        }
        if (preg_match('/CONTENU:\s*(.*)$/is', $raw, $m) === 1) {
            $content = trim($m[1]);
        }

        if ($title === '' || $content === '') {
            throw new RuntimeException("Réponse de l'IA inattendue : impossible d'en extraire un article.");
        }

        return [
            'title'   => mb_substr($title, 0, 255),
            'excerpt' => mb_substr($excerpt, 0, 500),
            'content' => HtmlSanitizer::clean($content),
        ];
    }
}
