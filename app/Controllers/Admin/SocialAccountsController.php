<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\SocialAccount;
use App\Models\SportsCompetition;

/**
 * Configuration des comptes réseaux sociaux connectés (v1 : jeton collé
 * manuellement, comme HOSTINGER_API_TOKEN) et des compétitions suivies
 * pour les résumés de matchs.
 */
final class SocialAccountsController extends Controller
{
    private SocialAccount $accounts;
    private SportsCompetition $competitions;

    public function __construct()
    {
        Auth::requireAuth();
        $this->accounts = new SocialAccount();
        $this->competitions = new SportsCompetition();
    }

    public function index(): void
    {
        $this->render('admin/social/accounts', [
            'title'        => 'Comptes réseaux sociaux — Espace SG',
            'accounts'     => $this->accounts->allOrdered(),
            'competitions' => $this->competitions->allOrdered(),
        ], 'layouts/admin');
    }

    public function save(): void
    {
        $this->verifyCsrf();

        $platform = (string) $this->input('platform');
        if (!in_array($platform, ['facebook', 'instagram', 'linkedin'], true)) {
            flash('error', 'Plateforme invalide.');
            $this->redirect('admin/social/comptes');
        }

        $token = trim((string) $this->input('access_token'));
        $ref = trim((string) $this->input('account_ref'));

        if ($token === '' || $ref === '') {
            flash('error', "Le jeton d'accès et l'identifiant du compte sont requis.");
            $this->redirect('admin/social/comptes');
        }

        $this->accounts->upsertForPlatform($platform, [
            'label'        => trim((string) $this->input('label')) ?: ucfirst($platform),
            'access_token' => $token,
            'account_ref'  => $ref,
            'is_active'    => 1,
        ]);

        flash('success', ucfirst($platform) . ' connecté.');
        $this->redirect('admin/social/comptes');
    }

    public function toggleCompetition(string $id): void
    {
        $this->verifyCsrf();
        $this->competitions->toggleActive((int) $id);
        flash('success', 'Compétition mise à jour.');
        $this->redirect('admin/social/comptes');
    }
}
