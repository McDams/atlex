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
            'title'       => 'Politique de confidentialité — ' . APP_NAME,
            'description' => 'Politique de confidentialité d\'ATLEX - Sport : données collectées, finalités, conservation et vos droits.',
        ]);
    }
}
