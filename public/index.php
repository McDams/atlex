<?php

declare(strict_types=1);

/**
 * Front controller unique de l'application ATLEX - Sport.
 */

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

// Chargement des variables d'environnement (.env facultatif).
if (is_file(ROOT . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(ROOT);
    $dotenv->safeLoad();
}

require ROOT . '/config/app.php';

use App\Core\Router;
use App\Core\Security;
use App\Core\Session;

Security::applyHeaders();
Session::start();

$router = new Router();
require ROOT . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);