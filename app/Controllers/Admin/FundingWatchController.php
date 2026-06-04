<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\FundingLead;
use App\Models\FundingOpportunity;
use App\Models\FundingSource;
use App\Models\Setting;
use App\Services\FundingWatchService;

/**
 * Veille de financements : opportunités détectées automatiquement depuis des
 * sources curées, à promouvoir dans le tracker de financements.
 */
final class FundingWatchController extends Controller
{
    private FundingLead $leads;

    public function __construct()
    {
        Auth::requireAuth();
        $this->leads = new FundingLead();
    }

    public function index(): void
    {
        $status = $this->input('status') ?: 'nouveau';
        $source = $this->input('source');
        $sourceId = is_string($source) && $source !== '' ? (int) $source : null;

        $this->render('admin/watch/index', [
            'title'   => 'Veille de financements — Espace SG',
            'leads'   => $this->leads->filtered(is_string($status) ? $status : 'nouveau', $sourceId),
            'sources' => (new FundingSource())->allOrdered(),
            'filterStatus' => $status,
            'filterSource' => $sourceId,
        ], 'layouts/admin');
    }

    /**
     * Déclenche une récupération immédiate des sources.
     */
    public function refresh(): void
    {
        $this->verifyCsrf();

        try {
            $report = (new FundingWatchService())->refresh();
            flash('success', sprintf(
                'Veille mise à jour : %d source(s) interrogée(s), %d nouvelle(s) opportunité(s).',
                $report['sources'],
                $report['new']
            ));
        } catch (\Throwable $e) {
            flash('error', 'Échec de la veille : ' . $e->getMessage());
        }

        $this->redirect('admin/veille');
    }

    /**
     * Promeut une opportunité détectée en financement suivi (avec checklist).
     */
    public function promote(string $id): void
    {
        $this->verifyCsrf();

        $lead = $this->leads->find((int) $id);
        if ($lead === null) {
            flash('error', 'Opportunité introuvable.');
            $this->redirect('admin/veille');
        }

        $opportunities = new FundingOpportunity();
        $notes = trim((string) ($lead['summary'] ?? ''));
        if (!empty($lead['source_name'])) {
            $notes = 'Source : ' . $lead['source_name'] . ($notes !== '' ? "\n\n" . $notes : '');
        }

        $oppId = $opportunities->create([
            'name'            => $lead['title'],
            'type'            => 'appel_projet',
            'status'          => 'identifie',
            'application_url' => $lead['url'],
            'notes'           => $notes ?: null,
            'created_by'      => Auth::id(),
        ]);

        // Démarches à suivre (checklist générique réutilisable).
        $opportunities->seedChecklist($oppId, funding_checklist_steps());

        $this->leads->setStatus((int) $id, 'promu');
        flash('success', 'Opportunité ajoutée au suivi. Complétez les informations et les démarches.');
        $this->redirect('admin/financements/' . $oppId . '/edit');
    }

    public function ignore(string $id): void
    {
        $this->verifyCsrf();
        $this->leads->setStatus((int) $id, 'ignore');
        flash('success', 'Opportunité ignorée.');
        $this->redirect('admin/veille');
    }

    // -------------------------------------------------------------------------
    // Sources & modèle de démarches
    // -------------------------------------------------------------------------

    public function sources(): void
    {
        $this->render('admin/watch/sources', [
            'title'    => 'Sources de veille — Espace SG',
            'sources'  => (new FundingSource())->allOrdered(),
            'template' => (new Setting())->get('funding_checklist_template') ?: implode("\n", funding_checklist_steps()),
        ], 'layouts/admin');
    }

    public function storeSource(): void
    {
        $this->verifyCsrf();

        $data = [
            'name'      => (string) $this->input('name'),
            'type'      => $this->input('type') === 'rss' ? 'rss' : 'google_news',
            'url'       => $this->input('url') ?: null,
            'query'     => $this->input('query') ?: null,
            'is_active' => 1,
        ];

        $validator = new Validator($data);
        $validator->validate(['name' => 'required|max:150', 'type' => 'in:rss,google_news']);
        if ($validator->fails()) {
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect('admin/veille/sources');
        }

        // Une source RSS exige une URL ; une source Google Actualités exige une requête.
        if ($data['type'] === 'rss' && empty($data['url'])) {
            flash('error', "Une source RSS nécessite l'URL du flux.");
            $this->redirect('admin/veille/sources');
        }
        if ($data['type'] === 'google_news' && empty($data['query'])) {
            flash('error', 'Une source Google Actualités nécessite une requête de recherche.');
            $this->redirect('admin/veille/sources');
        }

        (new FundingSource())->create($data);
        flash('success', 'Source ajoutée.');
        $this->redirect('admin/veille/sources');
    }

    public function updateSource(string $id): void
    {
        $this->verifyCsrf();
        $model = new FundingSource();

        if ($this->input('toggle') === '1') {
            $model->toggleActive((int) $id);
            flash('success', 'Source mise à jour.');
            $this->redirect('admin/veille/sources');
        }

        $this->redirect('admin/veille/sources');
    }

    public function destroySource(string $id): void
    {
        $this->verifyCsrf();
        (new FundingSource())->delete((int) $id);
        flash('success', 'Source supprimée.');
        $this->redirect('admin/veille/sources');
    }

    public function saveTemplate(): void
    {
        $this->verifyCsrf();
        (new Setting())->set('funding_checklist_template', (string) $this->input('template'));
        flash('success', 'Modèle de démarches enregistré.');
        $this->redirect('admin/veille/sources');
    }
}
