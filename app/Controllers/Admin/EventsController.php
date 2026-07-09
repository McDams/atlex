<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\Event;
use App\Models\EventCategory;

/**
 * CRUD des événements / calendrier.
 */
final class EventsController extends Controller
{
    private Event $model;
    private EventCategory $categories;

    public function __construct()
    {
        Auth::requireAuth();
        $this->model      = new Event();
        $this->categories = new EventCategory();
    }

    public function index(): void
    {
        $this->render('admin/events/index', [
            'title'  => 'Événements — Espace SG',
            'events' => $this->model->allOrdered(),
        ], 'layouts/admin');
    }

    public function create(): void
    {
        $this->render('admin/events/create', [
            'title'      => 'Nouvel événement — Espace SG',
            'categories' => $this->categories->allActive(),
        ], 'layouts/admin');
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/evenements/nouveau')) {
            return;
        }

        $this->model->create($data);
        flash('success', 'Événement créé.');
        $this->redirect('admin/evenements');
    }

    public function importIcs(): void
    {
        $this->verifyCsrf();

        if (
            !isset($_FILES['ics_file']) ||
            !is_array($_FILES['ics_file']) ||
            empty($_FILES['ics_file']['tmp_name'])
        ) {
            flash('error', 'Aucun fichier ICS envoyé.');
            $this->redirect('admin/evenements');
            return;
        }

        $rows = $this->parseIcsFile((string) $_FILES['ics_file']['tmp_name']);

        if ($rows === []) {
            flash('error', 'Le fichier ICS est vide ou invalide.');
            $this->redirect('admin/evenements');
            return;
        }

        $imported = 0;
        $updated = 0;

        foreach ($rows as $row) {
            if (empty($row['uid']) || empty($row['start_datetime'])) {
                continue;
            }

            $title = !empty($row['title']) ? (string) $row['title'] : 'Événement';

            $data = [
                'title'          => $title,
                'slug'           => slugify($title . '-' . substr((string) $row['uid'], 0, 8)),
                'type'           => 'tournoi',
                'discipline'     => 'tous',
                'category_id'    => null,
                'description'    => !empty($row['description']) ? (string) $row['description'] : null,
                'start_datetime' => (string) $row['start_datetime'],
                'end_datetime'   => !empty($row['end_datetime']) ? (string) $row['end_datetime'] : null,
                'location'       => !empty($row['location']) ? (string) $row['location'] : null,
                'is_published'   => 1,
                'external_uid'   => (string) $row['uid'],
                'source'         => 'ics',
            ];

            $existing = $this->model->findByExternalUid((string) $row['uid']);

            if ($existing !== null) {
                $this->model->update((int) $existing['id'], $data);
                $updated++;
            } else {
                $this->model->create($data);
                $imported++;
            }
        }

        flash('success', $imported . ' événement(s) importé(s), ' . $updated . ' mis à jour.');
        $this->redirect('admin/evenements');
    }

    public function edit(string $id): void
    {
        $event = $this->model->find((int) $id);
        if ($event === null) {
            flash('error', 'Événement introuvable.');
            $this->redirect('admin/evenements');
            return;
        }

        $this->render('admin/events/edit', [
            'title'      => 'Modifier un événement — Espace SG',
            'event'      => $event,
            'categories' => $this->categories->allActive(),
        ], 'layouts/admin');
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();
        $data = $this->payload();

        if (!$this->validate($data, 'admin/evenements/' . $id . '/edit')) {
            return;
        }

        $this->model->update((int) $id, $data);
        flash('success', 'Événement mis à jour.');
        $this->redirect('admin/evenements');
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $this->model->delete((int) $id);
        flash('success', 'Événement supprimé.');
        $this->redirect('admin/evenements');
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        $title      = (string) $this->input('title');
        $categoryId = $this->input('category_id');

        return [
            'title'          => $title,
            'slug'           => slugify($title),
            'type'           => $this->input('type') ?: 'autre',
            'discipline'     => $this->input('discipline') ?: 'tous',
            'category_id'    => $categoryId !== '' && $categoryId !== null ? (int) $categoryId : null,
            'description'    => $this->input('description') ?: null,
            'start_datetime' => $this->normalizeDateTime($this->input('start_datetime')),
            'end_datetime'   => $this->normalizeDateTime($this->input('end_datetime')) ?: null,
            'location'       => $this->input('location') ?: null,
            'is_published'   => $this->input('is_published') ? 1 : 0,
        ];
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        if (!is_string($value) || $value === '') {
            return null;
        }

        return str_replace('T', ' ', $value);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function validate(array $data, string $redirectPath): bool
    {
        $validator = new Validator($data);
        $validator->validate([
            'title'          => 'required|max:200',
            'start_datetime' => 'required',
            'type'           => 'in:match,tournoi,stage,entrainement,remise,autre',
            'discipline'     => 'in:basketball,handball,arts_martiaux,tous',
        ]);

        if ($validator->fails()) {
            set_old($data);
            flash('error', implode(' ', $validator->flatErrors()));
            $this->redirect($redirectPath);
        }

        clear_old();
        return true;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function parseIcsFile(string $filePath): array
    {
        $lines = @file($filePath, FILE_IGNORE_NEW_LINES);

        if ($lines === false) {
            return [];
        }

        $events = [];
        $event = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $event = [];
                continue;
            }

            if ($line === 'END:VEVENT') {
                if ($event !== null) {
                    $events[] = $event;
                }
                $event = null;
                continue;
            }

            if ($event === null || $line === '') {
                continue;
            }

            [$keyPart, $value] = array_pad(explode(':', $line, 2), 2, null);

            if ($value === null) {
                continue;
            }

            $key = strtoupper((string) explode(';', $keyPart)[0]);

            switch ($key) {
                case 'UID':
                    $event['uid'] = trim($value);
                    break;

                case 'SUMMARY':
                    $event['title'] = trim($value);
                    break;

                case 'DESCRIPTION':
                    $event['description'] = str_replace('\n', "\n", trim($value));
                    break;

                case 'LOCATION':
                    $event['location'] = trim($value);
                    break;

                case 'DTSTART':
                    $event['start_datetime'] = $this->icsToMysqlDatetime($value);
                    break;

                case 'DTEND':
                    $event['end_datetime'] = $this->icsToMysqlDatetime($value);
                    break;
            }
        }

        return $events;
    }

    private function icsToMysqlDatetime(string $value): ?string
    {
        $value = trim($value);

        $formats = [
            'Ymd\THis\Z',
            'Ymd\THis',
            'Ymd',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value, new \DateTimeZone('UTC'));

            if ($date instanceof \DateTime) {
                $date->setTimezone(new \DateTimeZone('Europe/Paris'));

                if ($format === 'Ymd') {
                    return $date->format('Y-m-d 00:00:00');
                }

                return $date->format('Y-m-d H:i:s');
            }
        }

        return null;
    }
}