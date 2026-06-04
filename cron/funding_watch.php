#!/usr/bin/env php
<?php

/**
 * Script cron — Veille de financements
 *
 * Interroge les sources curées (RSS / Google Actualités) et enregistre les
 * nouvelles opportunités dans la file de veille (funding_leads).
 *
 * Usage :
 *   php /var/www/atlex-sport/cron/funding_watch.php
 *
 * Crontab recommandée (deux fois par jour, 7h et 19h) :
 *   0 7,19 * * * php /var/www/atlex-sport/cron/funding_watch.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
 *
 * Script autonome : il initialise lui-même l'environnement applicatif.
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);

define('ROOT', $rootDir);
define('APP_ENV', getenv('APP_ENV') ?: 'production');

$autoload = $rootDir . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "[ERREUR] vendor/autoload.php introuvable. Exécutez 'composer install'.\n");
    exit(1);
}
require $autoload;

// Chargement du .env (via phpdotenv si dispo, sinon parsing manuel)
$envFile = $rootDir . '/.env';
if (file_exists($envFile)) {
    if (class_exists(\Dotenv\Dotenv::class)) {
        \Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
    } else {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

$appConfig = $rootDir . '/config/app.php';
if (file_exists($appConfig)) {
    require $appConfig;
}

use App\Services\FundingWatchService;

echo sprintf("[%s] [funding_watch] Démarrage...\n", date('Y-m-d H:i:s'));

try {
    $report = (new FundingWatchService())->refresh();

    echo sprintf(
        "[%s] [funding_watch] Terminé : %d source(s), %d élément(s) lus, %d nouvelle(s) opportunité(s).\n",
        date('Y-m-d H:i:s'),
        $report['sources'],
        $report['fetched'],
        $report['new']
    );
    exit(0);
} catch (\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] [funding_watch] ERREUR : %s dans %s ligne %d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    fwrite(STDERR, $errorMsg);

    $logDir = ROOT . '/storage/logs';
    if (is_dir($logDir)) {
        file_put_contents($logDir . '/funding_watch.log', $errorMsg, FILE_APPEND | LOCK_EX);
    }

    exit(1);
}
