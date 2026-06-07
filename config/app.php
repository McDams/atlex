<?php

/**
 * Configuration générale de l'application ATLEX - Sport.
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

define('APP_NAME', $env('APP_NAME', 'ATLEX - Sport'));

// URL de base utilisée par le helper url(). En mode tunnel/proxy
// (APP_URL_DYNAMIC=true, ex. démo via ngrok), elle est dérivée de la requête
// entrante (en-têtes X-Forwarded-* du proxy) afin que liens et assets pointent
// vers le domaine public. Désactivé par défaut → production inchangée.
$appUrl = rtrim((string) $env('APP_URL', 'http://localhost:8000'), '/');

if ($env('APP_URL_DYNAMIC', false) === true && !empty($_SERVER['HTTP_HOST'])) {
    $fwdProto = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
    $scheme   = $fwdProto !== ''
        ? strtok($fwdProto, ',')
        : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $host = trim((string) strtok((string) ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST']), ','));
    if ($host !== '') {
        $appUrl = $scheme . '://' . $host;
    }
}

define('APP_URL', $appUrl);
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
