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

        $this->render('athletes/index', [
            'title'        => 'Nos athlètes — ' . APP_NAME,
            'athletes'     => $this->model->published($discipline),
            'counts'       => $this->model->publishedCountByDiscipline(),
            'discipline'   => $discipline,
        ]);
    }

    public function show(string $slug): void
    {
        $athlete = $this->model->profileBySlug($slug);

        if ($athlete === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Athlète introuvable']);
            return;
        }

        $this->render('athletes/show', [
            'title'   => $athlete['first_name'] . ' ' . $athlete['last_name'] . ' — ' . APP_NAME,
            'athlete' => $athlete,
        ]);
    }
}
