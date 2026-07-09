<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ContactSubmission;
use App\Models\Event;
use App\Models\Member;
use App\Models\Task;
use PDO;

/**
 * Tableau de bord du Secrétariat Général.
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireAuth();

        $submissions = new ContactSubmission();

        $this->render('admin/dashboard', [
            'title'                => 'Tableau de bord — Espace SG',
            'memberCount'          => (new Member())->countActive(),
            'eventCount'           => (new Event())->countUpcoming(),
            'unreadCount'          => $submissions->countUnread(),
            'pendingInscriptions'  => $submissions->countPendingInscriptions(),
            'taskCount'            => (new Task())->countInProgress(),
            'recentTasks'          => (new Task())->recent(5),
            'recentContact'        => $submissions->recent(5),

            // KPI trafic résumé pour le dashboard principal
            'visitCount'           => $this->getVisitCount(),
        ], 'layouts/admin');
    }

    /**
     * Retourne le nombre total de pages vues du site.
     */
    private function getVisitCount(): int
    {
        $db = new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] .
            ';port=' . ($_ENV['DB_PORT'] ?? '3306') .
            ';dbname=' . $_ENV['DB_NAME'] .
            ';charset=utf8mb4',
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        return (int) $db->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    }
}