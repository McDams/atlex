<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ContactSubmission;
use App\Models\Event;
use App\Models\Member;
use App\Models\Task;

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
            'title'              => 'Tableau de bord — Espace SG',
            'memberCount'        => (new Member())->countActive(),
            'eventCount'         => (new Event())->countUpcoming(),
            'unreadCount'        => $submissions->countUnread(),
            'pendingInscriptions' => $submissions->countPendingInscriptions(),
            'taskCount'          => (new Task())->countInProgress(),
            'recentTasks'        => (new Task())->recent(5),
            'recentContact'      => $submissions->recent(5),
        ], 'layouts/admin');
    }
}
