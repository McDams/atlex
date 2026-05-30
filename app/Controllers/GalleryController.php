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

        $this->render('gallery/index', [
            'title'    => 'Galerie — ' . APP_NAME,
            'photos'   => $photos,
            'category' => $category,
        ]);
    }
}
