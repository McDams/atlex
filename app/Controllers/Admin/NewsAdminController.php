<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Validator;
use App\Models\NewsArticle;
use App\Services\NewsDraftGeneratorService;
use RuntimeException;
use Throwable;

/**
 * CRUD des articles d'actualités (avec upload d'image et contenu HTML enrichi).
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

    /**
     * Génère un brouillon d'article avec l'IA à partir d'un résumé de faits,
     * dans le même style éditorial que le reste du site. Crée l'article en
     * brouillon (non publié) puis redirige vers l'édition normale — la
     * relecture et la publication restent des actions manuelles.
     */
    public function generateDraft(): void
    {
        $this->verifyCsrf();

        $brief = trim((string) $this->input('brief'));
        if ($brief === '') {
            flash('error', 'Décrivez le sujet ou les faits à mettre en article.');
            $this->redirect('admin/actualites');
        }

        $category = (string) ($this->input('category') ?: 'general');

        $service = new NewsDraftGeneratorService();
        if (!$service->isConfigured()) {
            flash('error', 'Clé API Gemini non configurée (GEMINI_API_KEY dans .env).');
            $this->redirect('admin/actualites');
        }

        try {
            $id = $service->generateFromBrief($brief, $category);
            flash('success', "Brouillon généré par l'IA — relisez-le avant de publier.");
            $this->redirect('admin/actualites/' . $id . '/edit');
        } catch (Throwable $e) {
            flash('error', 'Échec de la génération : ' . $e->getMessage());
            $this->redirect('admin/actualites');
        }
    }

    public function edit(string $id): void
    {
        $article = $this->model->find((int) $id);

        if ($article === null) {
            flash('error', 'Article introuvable.');
            $this->redirect('admin/actualites');
            return;
        }

        $this->render('admin/news/edit', [
            'title'   => 'Modifier l’article — Espace SG',
            'article' => $article,
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

        if ($this->input('toggle') === '1') {
            $this->model->togglePublished((int) $id);

            if ($this->isAjax()) {
                $this->json(['ok' => true, 'id' => (int) $id]);
                return;
            }

            flash('success', 'Statut de publication mis à jour.');
            $this->redirect('admin/actualites');
            return;
        }

        $existing = $this->model->find((int) $id);
        if ($existing === null) {
            flash('error', 'Article introuvable.');
            $this->redirect('admin/actualites');
            return;
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/actualites')) {
            return;
        }

        $uploaded = $this->handleUpload();
        if ($uploaded !== null) {
            $data['cover_image'] = $uploaded;
        }

        if (empty($data['published_at']) && $data['is_published'] && empty($existing['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
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
        $title = trim((string) $this->input('title'));
        $excerpt = trim((string) ($this->input('excerpt') ?? ''));
        $content = (string) ($this->input('content') ?? '');

        $data = [
            'title'        => $title,
            'slug'         => slugify($title),
            'excerpt'      => $excerpt !== '' ? $excerpt : null,
            'content'      => $content !== '' ? $this->sanitizeArticleHtml($content) : null,
            'category'     => $this->input('category') ?: 'general',
            'is_published' => $this->input('is_published') ? 1 : 0,
        ];

        $date = trim((string) $this->input('published_at'));
        if ($date !== '') {
            $ts = strtotime($date);
            if ($ts !== false) {
                $data['published_at'] = date('Y-m-d H:i:s', $ts);
            }
        }

        return $data;
    }

    private function sanitizeArticleHtml(string $html): string
    {
        return \App\Core\HtmlSanitizer::clean($html);
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
            'category' => 'in:resultat,recrutement,evenement,partenariat,general,rapport,coupe du monde',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect($redirectPath);
            return false;
        }

        clear_old();
        return true;
    }
}