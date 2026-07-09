<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Visit;
use App\Models\VisitSession;
use App\Models\VisitStat;
use PDO;

final class AnalyticsController extends Controller
{
    public function index(): void
    {
        $pdo = new PDO(
            'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') .
            ';port=' . ($_ENV['DB_PORT'] ?? '3306') .
            ';dbname=' . ($_ENV['DB_NAME'] ?? '') .
            ';charset=utf8mb4',
            $_ENV['DB_USER'] ?? '',
            $_ENV['DB_PASS'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

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