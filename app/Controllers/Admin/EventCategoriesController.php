<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\EventCategory;

/**
 * CRUD des catégories d'événements.
 */
final class EventCategoriesController extends Controller
{
    private EventCategory $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new EventCategory();
    }

    public function index(): void
    {
        $this->render('admin/event_categories/index', [
            'title'      => 'Catégories d\'événements — Espace SG',
            'categories' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        if (empty($data['name']) || empty($data['slug'])) {
            flash('error', 'Le nom et le slug sont requis.');
            $this->redirect('admin/evenements/categories');
            return;
        }

        $this->model->create($data);
        flash('success', 'Catégorie créée.');
        $this->redirect('admin/evenements/categories');
    }

    public function edit(string $id): void
    {
        $category = $this->model->find((int) $id);
        if ($category === null) {
            flash('error', 'Catégorie introuvable.');
            $this->redirect('admin/evenements/categories');
        }

        $this->render('admin/event_categories/edit', [
            'title'    => 'Modifier une catégorie — Espace SG',
            'category' => $category,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        $data['is_active'] = isset($data['is_active']) ? 1 : 0;
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        $this->model->update((int) $id, $data);
        flash('success', 'Catégorie mise à jour.');
        $this->redirect('admin/evenements/categories');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Catégorie supprimée.');
        $this->redirect('admin/evenements/categories');
    }
}
