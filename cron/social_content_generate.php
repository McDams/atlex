#!/usr/bin/env php
<?php

/**
 * Script cron — Génération de brouillons de posts réseaux sociaux
 *
 * Propose des brouillons (statut « brouillon ») à partir des dernières
 * actualités et événements publiés sur le site. Ne publie jamais rien :
 * chaque brouillon attend une validation manuelle dans /admin/social.
 *
 * Usage :
 *   php /var/www/atlex-sport/cron/social_content_generate.php
 *
 * Crontab recommandée (une fois par jour, 6h) :
 *   0 6 * * * php /var/www/atlex-sport/cron/social_content_generate.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
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

use App\Services\AiContentService;
use App\Services\SocialContentGeneratorService;

echo sprintf("[%s] [social_content_generate] Démarrage...\n", date('Y-m-d H:i:s'));

$ai = new AiContentService();
if (!$ai->isConfigured()) {
    fwrite(STDERR, "[social_content_generate] ANTHROPIC_API_KEY absent de .env — aucun brouillon généré.\n");
    exit(0); // pas une erreur : dégradation attendue tant que la clé n'est pas configurée
}

try {
    $report = (new SocialContentGeneratorService($ai))->generate();

    echo sprintf(
        "[%s] [social_content_generate] Terminé : %d brouillon(s) créé(s), %d déjà proposé(s), %d erreur(s).\n",
        date('Y-m-d H:i:s'),
        $report['created'],
        $report['skipped'],
        $report['errors']
    );
    exit(0);
} catch (\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] [social_content_generate] ERREUR : %s dans %s ligne %d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    fwrite(STDERR, $errorMsg);

    $logDir = ROOT . '/storage/logs';
    if (is_dir($logDir)) {
        file_put_contents($logDir . '/social_content_generate.log', $errorMsg, FILE_APPEND | LOCK_EX);
    }

    exit(1);
}
