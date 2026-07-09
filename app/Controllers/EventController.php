<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;

final class EventController extends BaseController
{
    public function show(string $slug): void
    {
        $eventModel = new Event();
        $event = $eventModel->findBySlug($slug);

        if (!$event) {
            http_response_code(404);
            $this->render('errors/404.php', [
                'title' => 'Événement introuvable | ' . APP_NAME,
                'description' => 'L’événement demandé est introuvable.',
                'canonical' => url('/calendrier'),
                'ogImage' => 'images/hero-bg.png',
                'ogType' => 'website',
                'metaRobots' => 'noindex, nofollow',
                'message' => 'Événement introuvable',
            ]);
            return;
        }

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

        $this->render('events/show.php', [
            'title' => (string) $event['title'] . ' | ' . APP_NAME,
            'description' => $description,
            'canonical' => url('/evenements/' . $event['slug']),
            'ogImage' => $ogImage,
            'ogType' => 'article',
            'metaRobots' => 'index, follow',
            'event' => $event,
        ]);
    }
}