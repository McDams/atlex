<?php

/**
 * Constantes applicatives globales.
 *
 * BASE_URL et les constantes principales sont définies dans config/app.php.
 * Ce fichier expose des constantes secondaires réutilisables partout.
 */

if (!defined('ROOT')) {
    // Filet de sécurité : permet à l'autoload Composer (fichiers "files")
    // de s'exécuter avant que le point d'entrée n'ait défini ROOT — c'est
    // le cas lorsque PHPUnit charge vendor/autoload.php avant son bootstrap.
    define('ROOT', dirname(__DIR__, 2));
}

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
