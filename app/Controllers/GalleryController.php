<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\GalleryPhoto;

/**
 * Galerie photos publique.
 */
final class GalleryController extends Controller
{
    public function index(): void
    {
        $category = $this->input('categorie');
        $category = is_string($category) && $category !== '' ? $category : null;

        $photos = (new GalleryPhoto())->published();

        $title = 'Galerie photos | ' . APP_NAME;
        $description = 'Découvrez la galerie photo de ATLEX - Sport : événements, entraînements, compétitions, jeunesse et temps forts de l’association.';
        $canonical = url('/galerie');

        if ($category !== null) {
            $label = ucfirst(str_replace(['-', '_'], ' ', $category));
            $title = 'Galerie ' . $label . ' | ' . APP_NAME;
            $description = 'Découvrez les photos de la catégorie ' . $label . ' dans la galerie de ATLEX - Sport.';
            $canonical = url('/galerie?categorie=' . urlencode($category));
        }

        $ogImage = 'images/hero-bg.png';

        if (!empty($photos) && !empty($photos[0]['image'])) {
            $ogImage = (string) $photos[0]['image'];
        } elseif (!empty($photos) && !empty($photos[0]['file_path'])) {
            $ogImage = (string) $photos[0]['file_path'];
        }

        $this->render('gallery/index', [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'ogImage' => $ogImage,
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
            'photos' => $photos,
            'category' => $category,
        ]);
    }
}