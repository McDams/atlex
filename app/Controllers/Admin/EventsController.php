<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Event;
use App\Models\EventCategory;

/**
 * CRUD des événements / calendrier.
 */
final class EventsController extends Controller
{
    private Event $model;
    private EventCategory $categories;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model      = new Event();
        $this->categories = new EventCategory();
    }

    public function index(): void
    {
        $this->render('admin/events/index', [
            'title'  => 'Événements — Espace SG',
            'events' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/events/create', [
            'title'      => 'Nouvel événement — Espace SG',
            'categories' => $this->categories->allActive(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/evenements/nouveau')) {
            return;
        }

        $this->model->create($data);
        flash('success', 'Événement créé.');
        $this->redirect('admin/evenements');
    }

    public function edit(string $id): void
    {
        $event = $this->model->find((int) $id);
        if ($event === null) {
            flash('error', 'Événement introuvable.');
            $this->redirect('admin/evenements');
        }

        $this->render('admin/events/edit', [
            'title'      => 'Modifier un événement — Espace SG',
            'event'      => $event,
            'categories' => $this->categories->allActive(),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/evenements/' . $id . '/edit')) {
            return;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Événement mis à jour.');
        $this->redirect('admin/evenements');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Événement supprimé.');
        $this->redirect('admin/evenements');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        $title      = (string) $this->input('title');
        $categoryId = $this->input('category_id');

        return [
            'title'          => $title,
            'slug'           => slugify($title),
            'type'           => $this->input('type') ?: 'autre',
            'discipline'     => $this->input('discipline') ?: 'tous',
            'category_id'    => $categoryId !== '' && $categoryId !== null ? (int) $categoryId : null,
            'description'    => $this->input('description') ?: null,
            'start_datetime' => $this->normalizeDateTime($this->input('start_datetime')),
            'end_datetime'   => $this->normalizeDateTime($this->input('end_datetime')) ?: null,
            'location'       => $this->input('location') ?: null,
            'is_published'   => $this->input('is_published') ? 1 : 0,
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return str_replace('T', ' ', $value);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'title'          => 'required|max:200',
            'start_datetime' => 'required',
            'type'           => 'in:match,tournoi,stage,entrainement,remise,autre',
            'discipline'     => 'in:basketball,handball,arts_martiaux,tous',
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
