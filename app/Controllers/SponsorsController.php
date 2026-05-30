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
            'title'    => 'Sponsors & Partenaires — ' . APP_NAME,
            'sponsors' => (new Sponsor())->groupedByTier(),
        ]);
    }
}
