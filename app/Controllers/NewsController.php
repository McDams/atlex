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

        $this->render('news/index', [
            'title'      => 'Actualités — ' . APP_NAME,
            'articles'   => $articles,
            'featured'   => $articles[0] ?? null,
            'page'       => $page,
            'totalPages' => max(1, $totalPages),
            'category'   => $category,
        ]);
    }

    public function show(string $slug): void
    {
        $model = new NewsArticle();
        $article = $model->findBySlug($slug);

        if ($article === null || !$article['is_published']) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Article introuvable']);
            return;
        }

        $this->render('news/show', [
            'title'   => $article['title'] . ' — ' . APP_NAME,
            'article' => $article,
            'related' => $model->latest(3),
        ]);
    }
}
