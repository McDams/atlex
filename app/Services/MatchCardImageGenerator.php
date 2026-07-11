<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Génère une image de couverture (carte de score) pour un article de résumé
 * de match — bandeau titre, score, buteurs. Entièrement généré côté serveur
 * en SVG (aucune image externe téléchargée) : pas de question de droits
 * d'auteur, contrairement à une vraie photo de match qui appartiendrait à
 * une agence de presse.
 */
final class MatchCardImageGenerator
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    /**
     * @param array<int,array{team:string,scorer:string,minute:int}> $goalEvents
     */
    public function generate(
        string $homeTeam,
        string $awayTeam,
        int $homeScore,
        int $awayScore,
        string $competitionName,
        string $dateLabel,
        array $goalEvents = []
    ): string {
        $width = self::WIDTH;
        $height = self::HEIGHT;

        $homeGoals = array_values(array_filter($goalEvents, static fn (array $g): bool => $g['team'] === 'home'));
        $awayGoals = array_values(array_filter($goalEvents, static fn (array $g): bool => $g['team'] === 'away'));

        $homeGoalsSvg = $this->goalLines($homeGoals, 'start', 100);
        $awayGoalsSvg = $this->goalLines($awayGoals, 'end', $width - 100);

        $title = $this->esc($homeTeam) . ' – ' . $this->esc($awayTeam);
        $subtitle = $this->esc($competitionName) . ' · ' . $this->esc($dateLabel);
        $homeLabel = $this->esc($this->wrap($homeTeam));
        $awayLabel = $this->esc($this->wrap($awayTeam));
        $score = (int) $homeScore . ' – ' . (int) $awayScore;
        $centerX = (int) ($width / 2);

        return <<<SVG
        <svg viewBox="0 0 {$width} {$height}" xmlns="http://www.w3.org/2000/svg" font-family="Arial, Helvetica, sans-serif">
            <rect x="0" y="0" width="{$width}" height="{$height}" fill="#0a0e1a" />
            <rect x="0" y="0" width="{$width}" height="140" fill="#001a3d" />
            <text x="60" y="88" fill="#ffffff" font-size="46" font-weight="700">{$title}</text>

            <text x="60" y="200" fill="#ffffff" fill-opacity="0.55" font-size="26">{$subtitle}</text>
            <text x="{$centerX}" y="290" fill="#E53935" font-size="22" font-weight="700" text-anchor="middle" letter-spacing="2">TERMINÉ</text>

            <line x1="60" y1="230" x2="1140" y2="230" stroke="#ffffff" stroke-opacity="0.08" stroke-width="1" />

            <text x="270" y="340" fill="#ffffff" font-size="32" font-weight="600" text-anchor="middle">{$homeLabel}</text>
            <text x="{$centerX}" y="360" fill="#ffffff" font-size="110" font-weight="800" text-anchor="middle">{$score}</text>
            <text x="930" y="340" fill="#ffffff" font-size="32" font-weight="600" text-anchor="middle">{$awayLabel}</text>

            <line x1="60" y1="420" x2="1140" y2="420" stroke="#ffffff" stroke-opacity="0.08" stroke-width="1" />

            {$homeGoalsSvg}
            {$awayGoalsSvg}
        </svg>
        SVG;
    }

    /**
     * Enregistre le SVG dans public/uploads/matches et retourne le chemin
     * relatif à stocker dans news_articles.cover_image / social_posts.media_path.
     */
    public function save(string $svg, string $slugHint): string
    {
        $dir = ROOT . '/public/uploads/matches';
        if (!is_dir($dir)) {
            mkdir($dir, 0o755, true);
        }

        $filename = slugify($slugHint) . '-' . bin2hex(random_bytes(6)) . '.svg';
        file_put_contents($dir . '/' . $filename, $svg);

        return 'uploads/matches/' . $filename;
    }

    /**
     * @param array<int,array{team:string,scorer:string,minute:int}> $goals
     */
    private function goalLines(array $goals, string $anchor, int $x): string
    {
        if ($goals === []) {
            return '';
        }

        $lines = [];
        $y = 470;
        foreach (array_slice($goals, 0, 5) as $goal) {
            $label = $this->esc((string) $goal['scorer']) . ' ' . (int) $goal['minute'] . "'";
            $lines[] = sprintf(
                '<text x="%d" y="%d" fill="#ffffff" fill-opacity="0.8" font-size="22" text-anchor="%s">⚽ %s</text>',
                $x,
                $y,
                $anchor,
                $label
            );
            $y += 34;
        }

        return implode("\n", $lines);
    }

    private function wrap(string $team): string
    {
        return mb_strlen($team) > 18 ? mb_substr($team, 0, 17) . '…' : $team;
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
