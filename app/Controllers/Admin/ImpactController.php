<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\FundingOpportunity;
use App\Models\ImpactIndicator;
use App\Models\Member;
use App\Models\NewsArticle;
use App\Models\Project;
use App\Models\Sponsor;

/**
 * Tableau de bord d'impact (Espace SG) : indicateurs agrégés automatiquement
 * + indicateurs saisis manuellement par le SG.
 */
final class ImpactController extends Controller
{
    private ImpactIndicator $indicators;

    public function __construct()
    {
        Auth::requireAuth();
        $this->indicators = new ImpactIndicator();
    }

    public function index(): void
    {
        $project = new Project();
        $member  = new Member();
        $athlete = new Athlete();
        $funding = (new FundingOpportunity())->dashboard();

        $projectStatus = $project->countByStatus();

        $this->render('admin/impact/index', [
            'title' => "Tableau de bord d'impact — Espace SG",
            // Chiffres-clés
            'beneficiaries'   => $project->sumBeneficiaries(),
            'activeMembers'   => $member->countActive(),
            'athleteCount'    => $athlete->count(['is_published' => 1]),
            'projectOngoing'  => $projectStatus['en_cours'] ?? 0,
            'projectDone'     => $projectStatus['termine'] ?? 0,
            'projectTotal'    => array_sum($projectStatus),
            'fundingObtained' => $funding['obtained'],
            'fundingPipeline' => $funding['pipeline'],
            'partnerCount'    => (new Sponsor())->count(['is_active' => 1]),
            'eventTotal'      => (new Event())->count(),
            'reportCount'     => (new NewsArticle())->countPublished('rapport'),
            // Répartitions
            'membersByDiscipline' => $member->statsByDiscipline(),
            'athletesByDiscipline' => $athlete->publishedCountByDiscipline(),
            'projectStatus'   => $projectStatus,
            // Indicateurs manuels
            'manual'          => $this->indicators->allOrdered(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $data = [
            'label'      => (string) $this->input('label'),
            'value'      => (string) $this->input('value'),
            'unit'       => $this->input('unit') ?: null,
            'sort_order' => $this->input('sort_order') !== '' ? (int) $this->input('sort_order') : 0,
        ];

        $validator = new Validator($data);
        $validator->validate([
            'label' => 'required|max:150',
            'value' => 'required|max:60',
        ]);

        if ($validator->fails()) {
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect('admin/impact');
        }

        $this->indicators->create($data);
        flash('success', 'Indicateur ajouté.');
        $this->redirect('admin/impact');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->indicators->delete((int) $id);
        flash('success', 'Indicateur supprimé.');
        $this->redirect('admin/impact');
    }
}
