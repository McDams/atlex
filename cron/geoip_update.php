#!/usr/bin/env php
<?php

/**
 * Script cron — Mise à jour de la base de géolocalisation GeoLite2-City
 *
 * Télécharge la dernière base MaxMind GeoLite2-City et remplace
 * storage/geoip/GeoLite2-City.mmdb. Nécessite MAXMIND_LICENSE_KEY dans .env
 * (compte gratuit sur https://www.maxmind.com/en/geolite2/signup).
 *
 * Usage :
 *   php /var/www/atlex-sport/cron/geoip_update.php
 *
 * Crontab recommandée (MaxMind republie ~2x/mois — 1x/mois suffit) :
 *   0 4 5 * * php /var/www/atlex-sport/cron/geoip_update.php >> /var/www/atlex-sport/storage/logs/cron.log 2>&1
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

$licenseKey = (string) ($_ENV['MAXMIND_LICENSE_KEY'] ?? getenv('MAXMIND_LICENSE_KEY') ?: '');

if ($licenseKey === '') {
    fwrite(STDERR, "[geoip_update] ERREUR : MAXMIND_LICENSE_KEY absent de .env\n");
    exit(1);
}

$targetDir = $rootDir . '/storage/geoip';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0o755, true);
}

$targetFile = $targetDir . '/GeoLite2-City.mmdb';
$tmpArchive = $targetDir . '/GeoLite2-City.tar.gz';
$extractDir = $targetDir . '/extract_' . time();

echo sprintf("[%s] [geoip_update] Téléchargement de la base GeoLite2-City...\n", date('Y-m-d H:i:s'));

$url = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-City&license_key='
    . urlencode($licenseKey) . '&suffix=tar.gz';

$fp = fopen($tmpArchive, 'wb');
if ($fp === false) {
    fwrite(STDERR, "[geoip_update] ERREUR : impossible d'écrire dans $tmpArchive\n");
    exit(1);
}

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_FILE           => $fp,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_FAILONERROR    => true,
]);
$success = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);
fclose($fp);

if ($success === false || $httpCode !== 200) {
    fwrite(STDERR, sprintf(
        "[geoip_update] ERREUR : téléchargement échoué (HTTP %d) %s\n",
        $httpCode,
        $curlError
    ));
    @unlink($tmpArchive);
    exit(1);
}

try {
    $phar = new PharData($tmpArchive);
    $phar->extractTo($extractDir, null, true);

    $mmdbFiles = glob($extractDir . '/*/GeoLite2-City.mmdb');
    if (empty($mmdbFiles)) {
        throw new RuntimeException('Fichier GeoLite2-City.mmdb introuvable dans l\'archive téléchargée.');
    }

    if (!rename($mmdbFiles[0], $targetFile)) {
        throw new RuntimeException("Impossible de déplacer la base vers $targetFile");
    }

    echo sprintf("[%s] [geoip_update] Base mise à jour avec succès (%s).\n", date('Y-m-d H:i:s'), $targetFile);
    $exitCode = 0;
} catch (\Throwable $e) {
    fwrite(STDERR, sprintf("[geoip_update] ERREUR : %s\n", $e->getMessage()));
    $exitCode = 1;
} finally {
    @unlink($tmpArchive);
    if (is_dir($extractDir)) {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($extractDir);
    }
}

exit($exitCode);
