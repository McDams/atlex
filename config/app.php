<?php

/**
 * Configuration générale de l'application ATLÉX-SPORT.
 *
 * Charge les variables d'environnement et définit les constantes globales.
 */

$env = static function (string $key, mixed $default = null): mixed {
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true'  => true,
        'false' => false,
        'null'  => null,
        default => $value,
    };
};

define('APP_NAME', $env('APP_NAME', 'ATLÉX-SPORT'));
define('APP_URL', rtrim((string) $env('APP_URL', 'http://localhost:8000'), '/'));
define('APP_ENV', $env('APP_ENV', 'production'));
define('APP_DEBUG', (bool) $env('APP_DEBUG', false));

/** URL de base utilisée par le helper url(). */
define('BASE_URL', APP_URL);

// Gestion des erreurs selon l'environnement.
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', ROOT . '/storage/logs/php-errors.log');
}

date_default_timezone_set('Africa/Porto-Novo');
