<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\FundingOpportunity;
use App\Models\Project;

/**
 * Recherche de financements — tracker interne SG des opportunités
 * (subventions, appels à projets, sponsoring) et de l'état des candidatures.
 */
final class FundingController extends Controller
{
    private FundingOpportunity $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new FundingOpportunity();
    }

    public function index(): void
    {
        $status  = $this->input('status') ?: null;
        $project = $this->input('projet');
        $projectId = is_string($project) && $project !== '' ? (int) $project : null;

        $this->render('admin/funding/index', [
            'title'        => 'Recherche de financements — Espace SG',
            'opportunities' => $this->model->allFiltered(is_string($status) ? $status : null, $projectId),
            'dashboard'    => $this->model->dashboard(),
            'projects'     => (new Project())->options(),
            'filterStatus' => $status,
            'filterProject' => $projectId,
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/funding/create', [
            'title'       => 'Nouvelle opportunité — Espace SG',
            'opportunity' => null,
            'projects'    => (new Project())->options(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/financements/nouveau')) {
            return;
        }

        $data['created_by'] = Auth::id();
        $this->model->create($data);
        flash('success', 'Opportunité de financement ajoutée.');
        $this->redirect('admin/financements');
    }

    public function edit(string $id): void
    {
        $opportunity = $this->model->find((int) $id);
        if ($opportunity === null) {
            flash('error', 'Opportunité introuvable.');
            $this->redirect('admin/financements');
        }

        $this->render('admin/funding/edit', [
            'title'       => 'Modifier une opportunité — Espace SG',
            'opportunity' => $opportunity,
            'projects'    => (new Project())->options(),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        if ($this->model->find((int) $id) === null) {
            flash('error', 'Opportunité introuvable.');
            $this->redirect('admin/financements');
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/financements/' . $id . '/edit')) {
            return;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Opportunité mise à jour.');
        $this->redirect('admin/financements');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Opportunité supprimée.');
        $this->redirect('admin/financements');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        $projectId = $this->input('project_id');

        return [
            'project_id'      => is_string($projectId) && $projectId !== '' ? (int) $projectId : null,
            'name'            => (string) $this->input('name'),
            'funder'          => $this->input('funder') ?: null,
            'type'            => $this->input('type') ?: 'subvention',
            'amount'          => $this->input('amount') !== '' ? (float) $this->input('amount') : null,
            'deadline'        => $this->input('deadline') ?: null,
            'status'          => $this->input('status') ?: 'identifie',
            'application_url' => $this->input('application_url') ?: null,
            'notes'           => $this->input('notes') ?: null,
        ];
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'name'   => 'required|max:200',
            'type'   => 'in:subvention,appel_projet,sponsoring,crowdfunding,don,bourse,prix,autre',
            'status' => 'in:identifie,en_preparation,depose,obtenu,refuse',
            'amount' => 'numeric',
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
