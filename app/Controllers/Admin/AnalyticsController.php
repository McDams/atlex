<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Visit;
use App\Models\VisitSession;
use App\Models\VisitStat;

final class AnalyticsController extends Controller
{
    public function index(): void
    {
        $pdo = Database::getInstance();

        $visit = new Visit($pdo);
        $session = new VisitSession($pdo);
        $stat = new VisitStat($pdo);

        $overview7 = $stat->overview(7);
        $overview30 = $stat->overview(30);

        $this->render('admin/analytics/index', [
            'title'        => 'Analytics',
            'overview7'    => $overview7,
            'overview30'   => $overview30,
            'dailySeries'  => $stat->dailySeries(30),
            'topPages'     => $visit->topPages(10),
            'topCountries' => $visit->topCountries(10),
            'topSources'   => $visit->topSources(10),
            'devices'      => $visit->deviceBreakdown(),
            'browsers'     => $visit->browserBreakdown(),
            'bounceRate'   => $session->bounceRate(),
        ], 'layouts/admin');
    }
}