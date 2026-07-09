<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Athlete;

/**
 * Présentation publique des athlètes (cartes + profils détaillés).
 */
final class AthletesController extends Controller
{
    private Athlete $model;

    public function __construct()
    {
        $this->model = new Athlete();
    }

    public function index(): void
    {
        $discipline = $this->input('discipline');
        $discipline = is_string($discipline) && array_key_exists($discipline, ATLEX_DISCIPLINES)
            ? $discipline
            : null;

        $title = 'Nos athlètes | ' . APP_NAME;
        $description = 'Découvrez les athlètes de ATLEX - Sport, leurs disciplines, leurs profils et leur parcours sportif.';

        if ($discipline !== null) {
            $label = discipline_label($discipline);
            $title = 'Athlètes ' . $label . ' | ' . APP_NAME;
            $description = 'Découvrez les athlètes de la discipline ' . $label . ' au sein de ATLEX - Sport.';
        }

        $canonical = url('/athletes');
        if ($discipline !== null) {
            $canonical .= '?discipline=' . urlencode($discipline);
        }

        $this->render('athletes/index', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'athletes' => $this->model->published($discipline),
            'counts' => $this->model->publishedCountByDiscipline(),
            'discipline' => $discipline,
        ]);
    }

    public function show(string $slug): void
    {
        $athlete = $this->model->profileBySlug($slug);

        if ($athlete === null) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'Athlète introuvable | ' . APP_NAME,
                'description' => 'Le profil d’athlète demandé est introuvable.',
                'canonical' => url('/athletes'),
                'ogImage' => 'images/hero-bg.png',
                'ogType' => 'website',
                'metaRobots' => 'noindex, nofollow',
            ]);
            return;
        }

        $fullName = trim(($athlete['first_name'] ?? '') . ' ' . ($athlete['last_name'] ?? ''));
        $discipline = $athlete['discipline'] ?? null;
        $disciplineLabel = discipline_label($discipline);

        $description = trim((string) ($athlete['bio'] ?? ''));

        if ($description === '') {
            $description = $fullName . ' est un athlète de ' . APP_NAME;
            if ($disciplineLabel !== '') {
                $description .= ' en ' . $disciplineLabel;
            }
            $description .= '.';
        }

        if (function_exists('mb_substr')) {
            $description = mb_substr($description, 0, 160);
        } else {
            $description = substr($description, 0, 160);
        }

        $ogImage = !empty($athlete['photo'])
            ? (string) $athlete['photo']
            : 'images/hero-bg.png';

        $this->render('athletes/show', [
            'title' => $fullName . ' | ' . APP_NAME,
            'description' => $description,
            'canonical' => url('/athletes/' . $slug),
            'ogImage' => $ogImage,
            'ogType' => 'profile',
            'metaRobots' => 'index, follow',
            'athlete' => $athlete,
        ]);
    }
}