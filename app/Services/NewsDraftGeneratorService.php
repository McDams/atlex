<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\HtmlSanitizer;
use App\Models\NewsArticle;
use RuntimeException;

/**
 * Rédige un brouillon d'article d'actualité à partir d'un résumé de faits
 * fourni par le SG, dans le style éditorial déjà utilisé sur le site
 * (accroche courte, paragraphes brefs, ton chaleureux et communautaire).
 *
 * Crée directement une ligne news_articles en brouillon (is_published = 0) :
 * la relecture, l'édition et la publication se font ensuite normalement
 * via /admin/actualites/{id}/edit, comme pour un article créé à la main.
 */
final class NewsDraftGeneratorService
{
    private const VALID_CATEGORIES = ['general', 'resultat', 'recrutement', 'evenement', 'partenariat', 'rapport'];

    private AiContentService $ai;
    private NewsArticle $news;

    public function __construct(?AiContentService $ai = null)
    {
        $this->ai = $ai ?? new AiContentService();
        $this->news = new NewsArticle();
    }

    public function isConfigured(): bool
    {
        return $this->ai->isConfigured();
    }

    /**
     * @return int identifiant de l'article brouillon créé
     */
    public function generateFromBrief(string $brief, string $category = 'general'): int
    {
        if (!in_array($category, self::VALID_CATEGORIES, true)) {
            $category = 'general';
        }

        $raw = $this->ai->draft($this->systemPrompt(), $this->userPrompt($brief), 900);
        $parsed = $this->parseResponse($raw);

        return $this->news->create([
            'title'        => $parsed['title'],
            'slug'         => slugify($parsed['title']),
            'excerpt'      => $parsed['excerpt'],
            'content'      => $parsed['content'],
            'category'     => $category,
            'is_published' => 0,
        ]);
    }

    private function systemPrompt(): string
    {
        return "Tu rédiges des articles d'actualité pour le site web de ATLEX - Sport, "
            . 'une association sportive à Cotonou (Bénin) : football, basketball, handball, arts martiaux. '
            . "Style éditorial du site à reproduire fidèlement : ton chaleureux, fier et communautaire ; "
            . 'phrase d\'accroche courte en ouverture ; 3 à 4 paragraphes courts (1 à 2 phrases chacun) ; '
            . 'se termine souvent par une phrase d\'appel à l\'action (rejoindre, venir encourager, s\'inscrire...). '
            . "N'invente aucun fait, chiffre, date, nom ou résultat : utilise uniquement les informations "
            . "fournies ci-dessous par l'utilisateur.\n\n"
            . "Réponds STRICTEMENT selon ce format, sans rien ajouter avant ni après :\n"
            . "TITRE: <titre court et percutant>\n"
            . "EXTRAIT: <une phrase résumant l'article, pour l'aperçu>\n"
            . "CONTENU:\n<le corps de l'article en HTML — uniquement des balises <p>, <h2>, <strong>, <em>, "
            . '<ul>/<li> — un paragraphe <p> par idée, jamais de <html>, <body> ni <div>>';
    }

    private function userPrompt(string $brief): string
    {
        return "Faits à mettre en article :\n" . trim($brief);
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
