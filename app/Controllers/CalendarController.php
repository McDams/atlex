<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Event;
use App\Models\EventCategory;

/**
 * Calendrier interactif et API événements.
 */
final class CalendarController extends Controller
{
    public function index(): void
    {
        $year  = (int) date('Y');
        $month = (int) date('n');

        $catModel       = new EventCategory();
        $eventModel     = new Event();
        $categories     = $catModel->allActive();
        $activeCategory = $_GET['categorie'] ?? null;

        // Filtre par catégorie si demandé
        if ($activeCategory) {
            $cat    = $catModel->findBySlug($activeCategory);
            $events = $cat ? $eventModel->byCategory((int)$cat['id']) : [];
        } else {
            $events = $eventModel->upcoming(8);
        }

        $this->render('calendar/index', [
            'title'          => 'Événements — ' . APP_NAME,
            'year'           => $year,
            'month'          => $month,
            'events'         => $events,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    /**
     * Retourne les événements d'un mois au format JSON (avec couleur de catégorie).
     */
    public function apiEvents(string $year, string $month): void
    {
        $year  = (int) $year;
        $month = (int) $month;

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->json(['error' => 'Paramètres invalides'], 422);
        }

        $events = (new Event())->forMonth($year, $month);

        $payload = array_map(static function (array $e): array {
            return [
                'id'             => (int) $e['id'],
                'title'          => $e['title'],
                'slug'           => $e['slug'],
                'type'           => $e['type'],
                'discipline'     => $e['discipline'],
                'category_name'  => $e['category_name'] ?? null,
                'category_color' => $e['category_color'] ?? '#4B5563',
                'start'          => $e['start_datetime'],
                'end'            => $e['end_datetime'],
                'location'       => $e['location'],
                'day'            => (int) date('j', strtotime((string) $e['start_datetime'])),
            ];
        }, $events);

        $this->json([
            'year'   => $year,
            'month'  => $month,
            'events' => $payload,
        ]);
    }
}
