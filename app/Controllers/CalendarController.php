<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;

/**
 * Calendrier interactif et API événements.
 */
final class CalendarController extends Controller
{
    public function index(): void
    {
        $year = (int) date('Y');
        $month = (int) date('n');

        $this->render('calendar/index', [
            'title'  => 'Calendrier — ' . APP_NAME,
            'year'   => $year,
            'month'  => $month,
            'events' => (new Event())->forMonth($year, $month),
        ]);
    }

    /**
     * Retourne les événements d'un mois au format JSON.
     */
    public function apiEvents(string $year, string $month): void
    {
        $year = (int) $year;
        $month = (int) $month;

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->json(['error' => 'Paramètres invalides'], 422);
        }

        $events = (new Event())->forMonth($year, $month);

        $payload = array_map(static function (array $e): array {
            return [
                'id'         => (int) $e['id'],
                'title'      => $e['title'],
                'slug'       => $e['slug'],
                'type'       => $e['type'],
                'discipline' => $e['discipline'],
                'start'      => $e['start_datetime'],
                'end'        => $e['end_datetime'],
                'location'   => $e['location'],
                'day'        => (int) date('j', strtotime((string) $e['start_datetime'])),
            ];
        }, $events);

        $this->json([
            'year'   => $year,
            'month'  => $month,
            'events' => $payload,
        ]);
    }
}
