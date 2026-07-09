<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\NewsArticle;

/**
 * Actualités publiques.
 */
final class NewsController extends Controller
{
    private const PER_PAGE = 12;

    public function index(): void
    {
        $model = new NewsArticle();

        $page = max(1, (int) ($this->input('page', 1)));
        $category = $this->input('categorie');
        $category = is_string($category) && $category !== '' ? $category : null;

        $articles = $model->paginate($page, self::PER_PAGE, $category);
        $total = $model->countPublished($category);
        $totalPages = (int) ceil($total / self::PER_PAGE);

        $pageTitle = 'Actualités sportives | ' . APP_NAME;
        $pageDescription = 'Suivez les actualités sportives, compétitions, formations et événements de ATLEX - Sport à Cotonou.';

        if ($category !== null) {
            $pageTitle = 'Actualités ' . ucfirst($category) . ' | ' . APP_NAME;
            $pageDescription = 'Découvrez les actualités de la catégorie ' . $category . ' publiées par ATLEX - Sport.';
        }

        $canonical = url('/actualites');
        if ($category !== null || $page > 1) {
            $query = [];
            if ($category !== null) {
                $query['categorie'] = $category;
            }
            if ($page > 1) {
                $query['page'] = $page;
            }
            $canonical .= '?' . http_build_query($query);
        }

        $this->render('news/index', [
            'title' => $pageTitle,
            'description' => $pageDescription,
            'canonical' => $canonical,
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'articles' => $articles,
            'featured' => $articles[0] ?? null,
            'page' => $page,
            'totalPages' => max(1, $totalPages),
            'category' => $category,
        ]);
    }

    public function show(string $slug): void
    {
        $model = new NewsArticle();
        $article = $model->findBySlug($slug);

        if ($article === null || !$article['is_published']) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'Article introuvable | ' . APP_NAME,
                'description' => 'L’article demandé est introuvable.',
                'canonical' => url('/actualites'),
                'ogImage' => 'images/hero-bg.png',
                'ogType' => 'website',
                'metaRobots' => 'noindex, nofollow',
            ]);
            return;
        }

        $description = trim((string) ($article['excerpt'] ?? ''));

        if ($description === '') {
            $plainText = trim(preg_replace('/\s+/', ' ', strip_tags((string) ($article['content'] ?? ''))) ?? '');

            if (function_exists('mb_substr')) {
                $description = mb_substr($plainText, 0, 155);
            } else {
                $description = substr($plainText, 0, 155);
            }
        }

        $ogImage = !empty($article['cover_image'])
            ? (string) $article['cover_image']
            : 'images/hero-bg.png';

        $this->render('news/show', [
            'title' => $article['title'] . ' | ' . APP_NAME,
            'description' => $description,
            'canonical' => url('/actualites/' . $article['slug']),
            'ogImage' => $ogImage,
            'ogType' => 'article',
            'metaRobots' => 'index, follow',
            'article' => $article,
            'related' => $model->latest(3),
        ]);
    }
}