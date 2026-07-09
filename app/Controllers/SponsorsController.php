<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Sponsor;

/**
 * Page des partenaires et offres de sponsoring.
 */
final class SponsorsController extends Controller
{
    public function index(): void
    {
        $this->render('sponsors/index', [
            'title' => 'Sponsors et partenaires | ' . APP_NAME,
            'description' => 'Découvrez les sponsors et partenaires de ATLEX - Sport et les opportunités de sponsoring pour soutenir le sport et la jeunesse à Cotonou.',
            'canonical' => url('/sponsors'),
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'sponsors' => (new Sponsor())->groupedByTier(),
        ]);
    }
}