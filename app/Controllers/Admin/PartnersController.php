<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\FileUpload;
use App\Core\Validator;
use App\Models\Sponsor;
use RuntimeException;

/**
 * CRUD des partenaires / sponsors (espace SG) — affichés sur la page publique.
 */
final class PartnersController extends Controller
{
    private Sponsor $model;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model = new Sponsor();
    }

    public function index(): void
    {
        $this->render('admin/partners/index', [
            'title'    => 'Partenaires — Espace SG',
            'partners' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/partners/create', [
            'title'   => 'Nouveau partenaire — Espace SG',
            'partner' => null,
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/partenaires/nouveau')) {
            return;
        }

        $data['logo'] = $this->handleUpload();
        $this->model->create($data);
        flash('success', 'Partenaire ajouté.');
        $this->redirect('admin/partenaires');
    }

    public function edit(string $id): void
    {
        $partner = $this->model->find((int) $id);
        if ($partner === null) {
            flash('error', 'Partenaire introuvable.');
            $this->redirect('admin/partenaires');
        }

        $this->render('admin/partners/edit', [
            'title'   => 'Modifier un partenaire — Espace SG',
            'partner' => $partner,
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        // Bascule rapide actif/masqué.
        if ($this->input('toggle') === '1') {
            $this->model->toggleActive((int) $id);
            flash('success', 'Visibilité mise à jour.');
            $this->redirect('admin/partenaires');
        }

        if ($this->model->find((int) $id) === null) {
            flash('error', 'Partenaire introuvable.');
            $this->redirect('admin/partenaires');
        }

        $data = $this->payload();
        if (!$this->validate($data, 'admin/partenaires/' . $id . '/edit')) {
            return;
        }

        $uploaded = $this->handleUpload();
        if ($uploaded !== null) {
            $data['logo'] = $uploaded;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Partenaire mis à jour.');
        $this->redirect('admin/partenaires');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Partenaire supprimé.');
        $this->redirect('admin/partenaires');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        return [
            'name'        => (string) $this->input('name'),
            'tier'        => $this->input('tier') ?: 'associe',
            'website_url' => $this->input('website_url') ?: null,
            'description' => $this->input('description') ?: null,
            'is_active'   => $this->input('is_active') ? 1 : 0,
            'sort_order'  => $this->input('sort_order') !== '' ? (int) $this->input('sort_order') : 0,
        ];
    }

    private function handleUpload(): ?string
    {
        if (empty($_FILES['logo']['name'])) {
            return null;
        }

        try {
            $uploader = new FileUpload(ROOT . '/public/uploads');
            $result = $uploader->store($_FILES['logo']);
            return 'uploads/' . $result['filename'];
        } catch (RuntimeException $e) {
            flash('error', 'Logo : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'name' => 'required|max:150',
            'tier' => 'in:officiel,associe,media',
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
