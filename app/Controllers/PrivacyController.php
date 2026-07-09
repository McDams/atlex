<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

/**
 * Page « Politique de confidentialité » (RGPD).
 */
final class PrivacyController extends Controller
{
    public function index(): void
    {
        $this->render('privacy/index', [
            'title' => 'Politique de confidentialité | ' . APP_NAME,
            'description' => 'Consultez la politique de confidentialité de ATLEX - Sport : données collectées, finalités, conservation et droits des utilisateurs.',
            'canonical' => url('/politique-de-confidentialite'),
            'ogImage' => 'images/hero-bg.png',
            'ogType' => 'website',
            'metaRobots' => 'index, follow',
        ]);
    }
}