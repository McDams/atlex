<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Setting;
use App\Services\HostingerService;

/**
 * Contrôleur d'administration pour le monitoring Hostinger.
 *
 * Protégé : toutes les actions vérifient la session admin.
 *
 * Routes :
 *   GET  /admin/hostinger          → index()
 *   POST /admin/hostinger/save     → save()
 *   POST /admin/hostinger/test     → test()
 */
final class HostingerController extends Controller
{
    // -------------------------------------------------------------------------
    // Actions
    // -------------------------------------------------------------------------

    /**
     * Affiche le tableau de bord de monitoring Hostinger.
     * Charge les abonnements, domaines et alertes via l'API.
     */
    public function index(): void
    {
        Auth::requireAuth();

        $setting       = new Setting();
        $token         = $setting->get('hostinger_api_token') ?? '';
        $service       = new HostingerService($token);

        $subscriptions = [];
        $domains       = [];
        $alerts        = ['subscriptions' => [], 'domains' => []];
        $apiError      = null;

        if ($token !== '') {
            try {
                $subscriptions = $service->getSubscriptions();
                $domains       = $service->getDomains();
                $alerts        = $service->getAlerts();
            } catch (\RuntimeException $e) {
                $apiError = $e->getMessage();
            }
        }

        $this->render('admin/hostinger/index', [
            'title'         => 'Monitoring Hostinger',
            'token'         => $token,
            'subscriptions' => $subscriptions,
            'domains'       => $domains,
            'alerts'        => $alerts,
            'apiError'      => $apiError,
        ], 'layouts/admin');
    }

    /**
     * Sauvegarde le token API Hostinger en base de données (table settings).
     * Méthode POST.
     */
    public function save(): void
    {
        Auth::requireAuth();
        $this->verifyCsrf();

        $token = $this->input('hostinger_api_token', '');

        if ($token === '') {
            flash('error', 'Le token API ne peut pas être vide.');
            $this->redirect('/admin/hostinger');
        }

        $setting = new Setting();
        $saved   = $setting->set('hostinger_api_token', $token);

        if ($saved) {
            flash('success', 'Token API Hostinger sauvegardé avec succès.');
        } else {
            flash('error', 'Erreur lors de la sauvegarde du token API.');
        }

        $this->redirect('/admin/hostinger');
    }

    /**
     * Teste la connexion à l'API Hostinger et retourne un JSON.
     * Utilisé en AJAX depuis la vue admin.
     * Méthode POST ou GET.
     */
    public function test(): never
    {
        Auth::requireAuth();

        // Récupère le token depuis POST (formulaire AJAX) ou la DB
        $tokenInput = $this->input('hostinger_api_token', '');
        if ($tokenInput === '') {
            $setting    = new Setting();
            $tokenInput = $setting->get('hostinger_api_token') ?? '';
        }

        $service = new HostingerService($tokenInput);
        $result  = $service->testConnection();

        $this->json($result);
    }
}
