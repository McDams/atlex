#!/usr/bin/env php
<?php

/**
 * Script cron — Rappels d'expiration Hostinger
 *
 * Envoie des emails de rappel automatiques pour les abonnements
 * et domaines Hostinger expirant dans 30, 14 ou 7 jours.
 *
 * Usage :
 *   php /var/www/atlex-sport/cron/hostinger_reminders.php
 *
 * Crontab recommandée (exécution quotidienne à 8h) :
 *   0 8 * * * php /var/www/atlex-sport/cron/hostinger_reminders.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
 *
 * Ce script est autonome : il initialise lui-même l'environnement
 * applicatif en chargeant le bootstrap de l'application.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// 1. Initialisation de l'environnement
// ---------------------------------------------------------------------------

// Résoudre le répertoire racine de l'application
// Le script est dans /cron/, le projet est un niveau plus haut
$rootDir = dirname(__DIR__);

// Démarrer la session si ce n'est pas déjà fait (requis par certains helpers)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le bootstrap de l'application (constantes, autoloader, .env, etc.)
$bootstrapFile = $rootDir . '/public/index.php';

// Si le bootstrap n'existe pas, définir les constantes manuellement
if (!file_exists($bootstrapFile)) {
    define('ROOT', $rootDir);
    define('APP_ENV', getenv('APP_ENV') ?: 'production');

    // Autoloader PSR-4 via Composer
    $autoload = $rootDir . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        fwrite(STDERR, "[ERREUR] vendor/autoload.php introuvable. Exécutez 'composer install'.\n");
        exit(1);
    }
    require $autoload;

    // Chargement manuel du .env
    $envFile = $rootDir . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
} else {
    // Utiliser la détection de CLI pour ne pas générer de sortie HTTP
    define('IS_CLI', PHP_SAPI === 'cli');

    // Charger uniquement les fichiers nécessaires sans le routeur
    define('ROOT', $rootDir);
    define('APP_ENV', getenv('APP_ENV') ?: 'production');

    $autoload = $rootDir . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        fwrite(STDERR, "[ERREUR] vendor/autoload.php introuvable. Exécutez 'composer install'.\n");
        exit(1);
    }
    require $autoload;

    // Chargement du .env
    $envFile = $rootDir . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key   = trim($key);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    // Charger la config de l'application si elle existe
    $appConfig = $rootDir . '/config/app.php';
    if (file_exists($appConfig)) {
        require $appConfig;
    }
}

// Définir des constantes manquantes si nécessaire
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', ROOT . '/app/Views');
}

// ---------------------------------------------------------------------------
// 2. Vérification des prérequis
// ---------------------------------------------------------------------------

use App\Models\Setting;
use App\Services\HostingerReminderService;

$setting = new Setting();
$token   = $setting->get('hostinger_api_token') ?? '';

if ($token === '') {
    $msg = sprintf(
        "[%s] [hostinger_reminders] Token API Hostinger non configuré. Script ignoré.\n",
        date('Y-m-d H:i:s')
    );
    echo $msg;
    exit(0);
}

// ---------------------------------------------------------------------------
// 3. Exécution des rappels
// ---------------------------------------------------------------------------

echo sprintf("[%s] [hostinger_reminders] Démarrage...\n", date('Y-m-d H:i:s'));

try {
    $service = new HostingerReminderService();
    $service->sendExpirationReminders();

    echo sprintf("[%s] [hostinger_reminders] Terminé avec succès.\n", date('Y-m-d H:i:s'));
    exit(0);
} catch (\Throwable $e) {
    $errorMsg = sprintf(
        "[%s] [hostinger_reminders] ERREUR FATALE : %s dans %s ligne %d\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    fwrite(STDERR, $errorMsg);

    // Log dans fichier
    $logDir = ROOT . '/storage/logs';
    if (is_dir($logDir)) {
        file_put_contents($logDir . '/hostinger.log', $errorMsg, FILE_APPEND | LOCK_EX);
    }

    exit(1);
}
