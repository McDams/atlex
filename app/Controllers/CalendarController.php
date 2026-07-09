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

        $catModel = new EventCategory();
        $eventModel = new Event();
        $categories = $catModel->allActive();
        $activeCategory = $_GET['categorie'] ?? null;

        if ($activeCategory) {
            $cat = $catModel->findBySlug($activeCategory);
            $events = $cat ? $eventModel->byCategory((int) $cat['id']) : [];
        } else {
            $events = $eventModel->upcoming(8);
        }

        $title = 'Calendrier des événements sportifs | ' . APP_NAME;
        $description = 'Consultez le calendrier des événements, compétitions, stages et activités sportives organisés par ATLEX - Sport.';
        $canonical = url('/calendrier');

        if (is_string($activeCategory) && $activeCategory !== '') {
            $title = 'Calendrier ' . ucfirst(str_replace('-', ' ', $activeCategory)) . ' | ' . APP_NAME;
            $description = 'Découvrez les événements de la catégorie ' . str_replace('-', ' ', $activeCategory) . ' dans le calendrier ATLEX - Sport.';
            $canonical = url('/calendrier?categorie=' . urlencode($activeCategory));
        }

        $this->render('calendar/index', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'year' => $year,
            'month' => $month,
            'events' => $events,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(string $id): void
    {
        $eventId = (int) $id;
        $eventModel = new Event();
        $event = $eventModel->find($eventId);

        if ($event === null || empty($event['is_published'])) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'Événement introuvable | ' . APP_NAME,
                'description' => 'L’événement demandé est introuvable.',
                'canonical' => url('/calendrier'),
                'ogImage' => 'images/hero-bg.png',
                'ogType' => 'website',
                'metaRobots' => 'noindex, nofollow',
            ]);
            return;
        }

        $categoryId = isset($event['category_id']) ? (int) $event['category_id'] : null;
        $related = $eventModel->findRelated($eventId, $categoryId, 3);

        $description = trim((string) ($event['excerpt'] ?? ''));

        if ($description === '') {
            $parts = [];

            if (!empty($event['title'])) {
                $parts[] = (string) $event['title'];
            }

            if (!empty($event['location'])) {
                $parts[] = 'Lieu : ' . (string) $event['location'];
            }

            if (!empty($event['start_datetime'])) {
                $timestamp = strtotime((string) $event['start_datetime']);
                if ($timestamp !== false) {
                    $parts[] = 'Date : ' . date('d/m/Y H:i', $timestamp);
                }
            }

            if (!empty($event['discipline'])) {
                $parts[] = 'Discipline : ' . (string) $event['discipline'];
            }

            $description = implode(' | ', $parts);
        }

        $ogImage = !empty($event['cover_image'])
            ? (string) $event['cover_image']
            : 'images/hero-bg.png';

        $slugOrId = !empty($event['slug']) ? (string) $event['slug'] : (string) $eventId;

        $this->render('calendar/show', [
            'title' => (string) $event['title'] . ' | ' . APP_NAME,
            'description' => $description,
            'canonical' => url('/calendrier/' . $slugOrId),
            'ogImage' => $ogImage,
            'ogType' => 'article',
            'metaRobots' => 'index, follow',
            'event' => $event,
            'related' => $related,
        ]);
    }

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
                'id' => (int) $e['id'],
                'title' => $e['title'],
                'slug' => $e['slug'],
                'type' => $e['type'],
                'discipline' => $e['discipline'],
                'category_name' => $e['category_name'] ?? null,
                'category_color' => $e['category_color'] ?? '#4B5563',
                'start' => $e['start_datetime'],
                'end' => $e['end_datetime'],
                'location' => $e['location'],
                'day' => (int) date('j', strtotime((string) $e['start_datetime'])),
            ];
        }, $events);

        $this->json([
            'year' => $year,
            'month' => $month,
            'events' => $payload,
        ]);
    }
}