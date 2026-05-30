<?php

/**
 * Constantes applicatives globales.
 *
 * BASE_URL et les constantes principales sont définies dans config/app.php.
 * Ce fichier expose des constantes secondaires réutilisables partout.
 */

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', ROOT . '/app/Views');
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', ROOT . '/public/uploads');
}

if (!defined('STORAGE_PATH')) {
    define('STORAGE_PATH', ROOT . '/storage');
}

if (!defined('ATLEX_DISCIPLINES')) {
    define('ATLEX_DISCIPLINES', [
        'football'      => 'Football',
        'basketball'    => 'Basketball',
        'handball'      => 'Handball',
        'arts_martiaux' => 'Arts Martiaux',
    ]);
}
