<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Page « À propos » de l'association.
 */
final class AboutController extends Controller
{
    public function index(): void
    {
        $this->render('about/index', [
            'title' => 'À propos — ' . APP_NAME,
            'timeline' => [
                ['year' => '2023', 'label' => 'Fondation le 26 août à Cotonou'],
                ['year' => '2024', 'label' => 'Structuration des 4 disciplines'],
                ['year' => '2025', 'label' => 'Premiers tournois et partenariats'],
                ['year' => '2026', 'label' => 'Plateforme numérique & expansion'],
            ],
            'values' => [
                ['title' => 'Énergie', 'text' => 'Le moteur de chaque entraînement et de chaque match.'],
                ['title' => 'Passion', 'text' => 'L\'amour du sport qui anime nos membres au quotidien.'],
                ['title' => 'Maîtrise', 'text' => 'L\'exigence technique et la rigueur de l\'excellence.'],
                ['title' => 'Collectif', 'text' => 'L\'union des forces au service d\'un même objectif.'],
            ],
        ]);
    }
}
