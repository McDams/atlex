#!/usr/bin/env php
<?php

/**
 * Provisionnement d'un API User + API Key MTN MoMo (bac à sable).
 *
 * Étape ponctuelle à faire une seule fois avant de pouvoir utiliser l'API
 * Collections : contrairement à la Subscription Key (disponible directement
 * dans le portail momodeveloper.mtn.com), l'API User et l'API Key doivent
 * être créés par un appel API — ce script le fait pour vous.
 *
 * Prérequis : MOMO_SUBSCRIPTION_KEY déjà renseignée dans .env (récupérée
 * dans momodeveloper.mtn.com → votre profil → Collections → Primary Key).
 *
 * Usage :
 *   php bin/momo-provision-sandbox.php
 *
 * En sortie : un API User et une API Key à copier dans .env
 * (MOMO_API_USER et MOMO_API_KEY). Chaque exécution crée un NOUVEL
 * utilisateur sandbox — ne relancez pas ce script une fois vos clés
 * obtenues et fonctionnelles.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Ce script doit être exécuté en ligne de commande.\n");
    exit(1);
}

$rootDir = dirname(__DIR__);
define('ROOT', $rootDir);

$autoload = $rootDir . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "[ERREUR] vendor/autoload.php introuvable. Exécutez « composer install ».\n");
    exit(1);
}
require $autoload;

if (is_file($rootDir . '/.env')) {
    Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}

$subscriptionKey = (string) ($_ENV['MOMO_SUBSCRIPTION_KEY'] ?? getenv('MOMO_SUBSCRIPTION_KEY') ?: '');

if ($subscriptionKey === '') {
    fwrite(STDERR, "[ERREUR] MOMO_SUBSCRIPTION_KEY absente de .env.\n");
    fwrite(STDERR, "Récupérez-la sur momodeveloper.mtn.com -> votre profil -> Collections -> Primary Key,\n");
    fwrite(STDERR, "ajoutez-la dans .env, puis relancez ce script.\n");
    exit(1);
}

const SANDBOX_BASE_URL = 'https://sandbox.momodeveloper.mtn.com';

/**
 * @param array<int,string> $headers
 * @return array{httpCode:int,body:string}
 */
function callMomo(string $method, string $path, ?string $body, array $headers): array
{
    $ch = curl_init(SANDBOX_BASE_URL . $path);
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_HTTPHEADER     => $headers,
    ];
    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        fwrite(STDERR, "[ERREUR] Requête réseau échouée : {$error}\n");
        exit(1);
    }

    return ['httpCode' => $httpCode, 'body' => (string) $response];
}

function generateUuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

echo "=== Provisionnement MTN MoMo (bac à sable) ===\n\n";

$apiUser = generateUuid();
echo "1/2 — Création de l'API User ({$apiUser})...\n";

$createUserResponse = callMomo(
    'POST',
    '/v1_0/apiuser',
    json_encode(['providerCallbackHost' => 'atlex-sport.com']),
    [
        'X-Reference-Id: ' . $apiUser,
        'Ocp-Apim-Subscription-Key: ' . $subscriptionKey,
        'Content-Type: application/json',
    ]
);

if ($createUserResponse['httpCode'] !== 201) {
    fwrite(STDERR, "[ERREUR] Échec de création de l'API User (HTTP {$createUserResponse['httpCode']}).\n");
    fwrite(STDERR, $createUserResponse['body'] . "\n");
    exit(1);
}

echo "    OK.\n\n";
echo "2/2 — Génération de l'API Key...\n";

$createKeyResponse = callMomo(
    'POST',
    '/v1_0/apiuser/' . $apiUser . '/apikey',
    '',
    ['Ocp-Apim-Subscription-Key: ' . $subscriptionKey]
);

if ($createKeyResponse['httpCode'] !== 201) {
    fwrite(STDERR, "[ERREUR] Échec de génération de l'API Key (HTTP {$createKeyResponse['httpCode']}).\n");
    fwrite(STDERR, $createKeyResponse['body'] . "\n");
    exit(1);
}

$decoded = json_decode($createKeyResponse['body'], true);
$apiKey = is_array($decoded) ? ($decoded['apiKey'] ?? null) : null;

if (!is_string($apiKey) || $apiKey === '') {
    fwrite(STDERR, "[ERREUR] Réponse inattendue, apiKey introuvable :\n" . $createKeyResponse['body'] . "\n");
    exit(1);
}

echo "    OK.\n\n";
echo "=== Terminé — ajoutez ces deux lignes dans .env ===\n\n";
echo "MOMO_API_USER={$apiUser}\n";
echo "MOMO_API_KEY={$apiKey}\n\n";
echo "MOMO_SUBSCRIPTION_KEY et MOMO_ENV=sandbox doivent déjà être présentes.\n";
