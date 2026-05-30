<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Task;

/**
 * Gestion des tâches internes (Kanban SG).
 */
final class TasksController extends Controller
{
    private Task $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Task();
    }

    public function index(): void
    {
        $this->render('admin/tasks/index', [
            'title' => 'Tâches — Espace SG',
            'board' => $this->model->groupedByStatus(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $data = [
            'title'       => $this->input('title'),
            'description' => $this->input('description') ?: null,
            'status'      => $this->input('status') ?: 'a_faire',
            'priority'    => $this->input('priority') ?: 'normale',
            'due_date'    => $this->input('due_date') ?: null,
            'created_by'  => Auth::id(),
        ];

        $validator = new Validator($data);
        $validator->validate([
            'title'    => 'required|max:250',
            'status'   => 'in:a_faire,en_cours,termine',
            'priority' => 'in:basse,normale,haute,urgente',
        ]);

        if ($validator->fails()) {
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect('admin/taches');
        }

        $this->model->create($data);
        flash('success', 'Tâche créée.');
        $this->redirect('admin/taches');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        // Mise à jour rapide du statut (drag-and-drop AJAX).
        $status = $this->input('status');
        if (is_string($status) && $status !== '' && $this->input('status_only') === '1') {
            $ok = $this->model->updateStatus((int) $id, $status);
            if ($this->isAjax()) {
                $this->json(['ok' => $ok, 'id' => (int) $id, 'status' => $status]);
            }
            $this->redirect('admin/taches');
        }

        $this->model->update((int) $id, [
            'title'       => $this->input('title'),
            'description' => $this->input('description') ?: null,
            'status'      => $this->input('status') ?: 'a_faire',
            'priority'    => $this->input('priority') ?: 'normale',
            'due_date'    => $this->input('due_date') ?: null,
        ]);

        flash('success', 'Tâche mise à jour.');
        $this->redirect('admin/taches');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Tâche supprimée.');
        $this->redirect('admin/taches');
    }

    private function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }
}
