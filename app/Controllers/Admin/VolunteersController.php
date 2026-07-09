<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\ContactController;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\VolunteerRequest;

/**
 * Gestion des candidatures bénévoles soumises depuis le site public.
 */
final class VolunteersController extends Controller
{
    private VolunteerRequest $volunteers;

    public function __construct()
    {
        Auth::requireAuth();
        $this->volunteers = new VolunteerRequest();
    }

    public function index(): void
    {
        $this->render('admin/volunteers/index', [
            'title'    => 'Bénévoles — Espace SG',
            'requests' => $this->volunteers->all(),
            'stats'    => $this->volunteers->countByStatus(),
            'missions' => ContactController::VOLUNTEER_MISSIONS,
        ], 'layouts/admin');
    }

    public function show(string $id): void
    {
        $request = $this->volunteers->find((int) $id);

        if ($request === null) {
            flash('error', 'Candidature introuvable.');
            $this->redirect('admin/benevoles');
        }

        $this->render('admin/volunteers/show', [
            'title'    => 'Candidature bénévole — Espace SG',
            'request'  => $request,
            'missions' => ContactController::VOLUNTEER_MISSIONS,
        ], 'layouts/admin');
    }

    public function updateStatus(string $id): void
    {
        $this->verifyCsrf();

        $status = (string) $this->input('status', '');

        if (!$this->volunteers->updateStatus((int) $id, $status)) {
            flash('error', 'Statut invalide ou candidature introuvable.');
            $this->redirect('admin/benevoles/' . (int) $id);
        }

        flash('success', 'Statut mis à jour.');
        $this->redirect('admin/benevoles/' . (int) $id);
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();

        $this->volunteers->delete((int) $id);

        flash('success', 'Candidature supprimée.');
        $this->redirect('admin/benevoles');
    }
}
