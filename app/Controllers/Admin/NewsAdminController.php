<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Validator;
use App\Models\NewsArticle;
use RuntimeException;

/**
 * CRUD des articles d'actualités (avec upload d'image).
 */
final class NewsAdminController extends Controller
{
    private NewsArticle $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new NewsArticle();
    }

    public function index(): void
    {
        $this->render('admin/news/index', [
            'title'    => 'Actualités — Espace SG',
            'articles' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/news/create', [
            'title' => 'Nouvel article — Espace SG',
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/actualites')) {
            return;
        }

        $data['cover_image'] = $this->handleUpload();
        $data['author_id'] = Auth::id();

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $this->model->create($data);
        flash('success', 'Article créé.');
        $this->redirect('admin/actualites');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        // Bascule rapide de l'état de publication (AJAX).
        if ($this->input('toggle') === '1') {
            $this->model->togglePublished((int) $id);
            if ($this->isAjax()) {
                $this->json(['ok' => true, 'id' => (int) $id]);
            }
            flash('success', 'Statut de publication mis à jour.');
            $this->redirect('admin/actualites');
        }

        $existing = $this->model->find((int) $id);
        if ($existing === null) {
            flash('error', 'Article introuvable.');
            $this->redirect('admin/actualites');
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/actualites')) {
            return;
        }

        $uploaded = $this->handleUpload();
        if ($uploaded !== null) {
            $data['cover_image'] = $uploaded;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Article mis à jour.');
        $this->redirect('admin/actualites');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Article supprimé.');
        $this->redirect('admin/actualites');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        $title = (string) $this->input('title');

        return [
            'title'        => $title,
            'slug'         => slugify($title),
            'excerpt'      => $this->input('excerpt') ?: null,
            'content'      => $this->input('content') ?: null,
            'category'     => $this->input('category') ?: 'general',
            'is_published' => $this->input('is_published') ? 1 : 0,
        ];
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['cover_image']['name'])) {
            return null;
        }

        try {
            $uploader = new FileUpload(ROOT . '/public/uploads');
            $result = $uploader->store($_FILES['cover_image']);
            return 'uploads/' . $result['filename'];
        } catch (RuntimeException $e) {
            flash('error', 'Image : ' . $e->getMessage());
            return null;
        }
    }

    private function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'title'    => 'required|max:250',
            'category' => 'in:resultat,recrutement,evenement,partenariat,general',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect($redirectPath);
        }

        clear_old();
        return true;
    }
}
