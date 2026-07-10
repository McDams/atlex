#!/usr/bin/env php
<?php

/**
 * Script cron — Vérification des résultats des grandes compétitions
 *
 * Interroge l'API-Football pour les matchs terminés des compétitions
 * actives (/admin/social/comptes) et propose des brouillons de résumés.
 * Ne publie jamais rien : chaque brouillon attend une validation manuelle.
 *
 * Usage :
 *   php /var/www/atlex-sport/cron/sports_results_check.php
 *
 * Crontab recommandée (toutes les 2h) :
 *   0 0,2,4,6,8,10,12,14,16,18,20,22 * * * php /var/www/atlex-sport/cron/sports_results_check.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
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

use App\Services\SportsResultsService;

echo sprintf("[%s] [sports_results_check] Démarrage...\n", date('Y-m-d H:i:s'));

$service = new SportsResultsService();
if (!$service->isConfigured()) {
    fwrite(STDERR, "[sports_results_check] API_FOOTBALL_KEY absent de .env — vérification ignorée.\n");
    exit(0); // pas une erreur : dégradation attendue tant que la clé n'est pas configurée
}

try {
    $report = $service->checkFinishedMatches();

    echo sprintf(
        "[%s] [sports_results_check] Terminé : %d compétition(s), %d match(s), %d brouillon(s) créé(s), %d erreur(s).\n",
        date('Y-m-d H:i:s'),
        $report['competitions'],
        $report['matches'],
        $report['created'],
        $report['errors']
    );
    exit(0);
} catch (\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] [sports_results_check] ERREUR : %s dans %s ligne %d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    fwrite(STDERR, $errorMsg);

    $logDir = ROOT . '/storage/logs';
    if (is_dir($logDir)) {
        file_put_contents($logDir . '/sports_results_check.log', $errorMsg, FILE_APPEND | LOCK_EX);
    }

    exit(1);
}
