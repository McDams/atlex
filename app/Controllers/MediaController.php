<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PressCoverage;
use App\Models\PressKitItem;
use App\Models\PressRelease;
use App\Models\Setting;

/**
 * Centre média public : communiqués, kit presse, revue de presse, contact presse.
 */
final class MediaController extends Controller
{
    public function index(): void
    {
        $contact = (new Setting())->getMany([
            'press_contact_name',
            'press_contact_email',
            'press_contact_phone',
        ]);

        // Regroupe les ressources du kit par catégorie.
        $kit = [];
        foreach ((new PressKitItem())->allOrdered() as $item) {
            $kit[(string) $item['category']][] = $item;
        }

        $this->render('media/index', [
            'title'    => 'Centre média — ' . APP_NAME,
            'releases' => (new PressRelease())->published(),
            'kit'      => $kit,
            'coverage' => (new PressCoverage())->allOrdered(),
            'contact'  => $contact,
        ]);
    }

    public function show(string $slug): void
    {
        $release = (new PressRelease())->publishedBySlug($slug);

        if ($release === null) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Communiqué introuvable']);
            return;
        }

        $this->render('media/show', [
            'title'   => $release['title'] . ' — ' . APP_NAME,
            'release' => $release,
        ]);
    }
}
