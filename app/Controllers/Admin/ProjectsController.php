<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\FundingOpportunity;
use App\Models\Project;

/**
 * CRUD des projets de l'association (gestion interne SG).
 */
final class ProjectsController extends Controller
{
    private Project $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Project();
    }

    public function index(): void
    {
        $this->render('admin/projects/index', [
            'title'    => 'Projets — Espace SG',
            'projects' => $this->model->allWithFunding(),
            'stats'    => $this->model->countByStatus(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/projects/create', [
            'title'    => 'Nouveau projet — Espace SG',
            'project'  => null,
            'funding'  => [],
            'partners' => [],
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/projets/nouveau')) {
            return;
        }

        $data['created_by'] = Auth::id();
        $id = $this->model->create($data);
        $this->model->syncPartners($id, $this->partnerRows());
        flash('success', 'Projet créé.');
        $this->redirect('admin/projets');
    }

    public function edit(string $id): void
    {
        $project = $this->model->find((int) $id);
        if ($project === null) {
            flash('error', 'Projet introuvable.');
            $this->redirect('admin/projets');
        }

        $this->render('admin/projects/edit', [
            'title'    => 'Modifier un projet — Espace SG',
            'project'  => $project,
            'funding'  => (new FundingOpportunity())->byProject((int) $id),
            'partners' => $this->model->partners((int) $id),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        if ($this->model->find((int) $id) === null) {
            flash('error', 'Projet introuvable.');
            $this->redirect('admin/projets');
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/projets/' . $id . '/edit')) {
            return;
        }

        $this->model->update((int) $id, $data);
        $this->model->syncPartners((int) $id, $this->partnerRows());
        flash('success', 'Projet mis à jour.');
        $this->redirect('admin/projets');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        // Les financements rattachés sont détachés (project_id -> NULL via FK).
        $this->model->delete((int) $id);
        flash('success', 'Projet supprimé.');
        $this->redirect('admin/projets');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        return [
            'title'             => (string) $this->input('title'),
            'description'       => $this->input('description') ?: null,
            'discipline'        => $this->input('discipline') ?: 'tous',
            'theme'             => $this->input('theme') ?: null,
            'status'            => $this->input('status') ?: 'planifie',
            'lead'              => $this->input('lead') ?: null,
            'beneficiaries'     => $this->input('beneficiaries') ?: null,
            'beneficiary_count' => $this->input('beneficiary_count') !== '' ? (int) $this->input('beneficiary_count') : null,
            'expected_impact'   => $this->input('expected_impact') ?: null,
            'budget_target'     => $this->input('budget_target') !== '' ? (float) $this->input('budget_target') : null,
            'start_date'        => $this->input('start_date') ?: null,
            'end_date'          => $this->input('end_date') ?: null,
        ];
    }

    /**
     * Recompose les partenaires à partir des tableaux POST parallèles.
     *
     * @return array<int,array<string,string>>
     */
    private function partnerRows(): array
    {
        $names = $_POST['pa_name'] ?? [];
        $roles = $_POST['pa_role'] ?? [];
        $names = is_array($names) ? array_values($names) : [];
        $roles = is_array($roles) ? array_values($roles) : [];

        $rows = [];
        foreach ($names as $i => $name) {
            $rows[] = [
                'name' => is_string($name) ? trim($name) : '',
                'role' => isset($roles[$i]) && is_string($roles[$i]) ? trim($roles[$i]) : '',
            ];
        }

        return $rows;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'title'             => 'required|max:200',
            'discipline'        => 'in:football,basketball,handball,arts_martiaux,tous',
            'theme'             => 'max:120',
            'status'            => 'in:planifie,en_cours,en_pause,termine,annule',
            'beneficiary_count' => 'numeric',
            'budget_target'     => 'numeric',
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
