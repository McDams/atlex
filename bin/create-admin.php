#!/usr/bin/env php
<?php

/**
 * Création / réinitialisation du compte administrateur ATLEX - Sport.
 *
 * Aucun mot de passe par défaut n'est livré avec le code : ce script crée
 * (ou met à jour) un compte admin avec un mot de passe FORT que vous choisissez.
 *
 * Usage interactif :
 *   php bin/create-admin.php
 *
 * Usage non interactif (CI / déploiement automatisé) :
 *   ADMIN_NAME="..." ADMIN_LOGIN_EMAIL="..." ADMIN_PASSWORD="..." php bin/create-admin.php
 *
 * Si l'email existe déjà, le mot de passe (et le nom) sont mis à jour.
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

// Chargement du .env (pour les identifiants de base de données).
if (is_file($rootDir . '/.env')) {
    Dotenv\Dotenv::createImmutable($rootDir)->safeLoad();
}
require $rootDir . '/config/app.php';

use App\Models\User;

/**
 * Lit une valeur depuis une variable d'environnement, sinon depuis l'entrée
 * standard (masquée pour les mots de passe lorsque c'est possible).
 */
$ask = static function (string $label, string $envKey, bool $secret = false): string {
    $fromEnv = getenv($envKey);
    if ($fromEnv !== false && $fromEnv !== '') {
        return trim($fromEnv);
    }

    fwrite(STDOUT, $label . ' : ');

    if ($secret && stripos(PHP_OS, 'WIN') === false && @shell_exec('which stty')) {
        shell_exec('stty -echo');
        $value = trim((string) fgets(STDIN));
        shell_exec('stty echo');
        fwrite(STDOUT, "\n");
        return $value;
    }

    return trim((string) fgets(STDIN));
};

echo "=== Création / réinitialisation du compte administrateur ===\n";

$name  = $ask('Nom affiché', 'ADMIN_NAME');
$email = $ask('Email de connexion', 'ADMIN_LOGIN_EMAIL');
$pass  = $ask('Mot de passe (min. 12 caractères)', 'ADMIN_PASSWORD', true);

// Validations minimales.
if ($name === '' || $email === '') {
    fwrite(STDERR, "[ERREUR] Le nom et l'email sont obligatoires.\n");
    exit(1);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "[ERREUR] Email invalide.\n");
    exit(1);
}
if (strlen($pass) < 12) {
    fwrite(STDERR, "[ERREUR] Le mot de passe doit comporter au moins 12 caractères.\n");
    exit(1);
}

try {
    $users    = new User();
    $existing = $users->findByEmail($email);

    if ($existing !== null) {
        $users->update((int) $existing['id'], [
            'name'     => $name,
            'password' => password_hash($pass, PASSWORD_BCRYPT),
            'role'     => 'admin',
        ]);
        echo "✅ Compte admin mis à jour : {$email}\n";
    } else {
        $id = $users->createWithHash([
            'name'     => $name,
            'email'    => $email,
            'password' => $pass,
            'role'     => 'admin',
        ]);
        echo "✅ Compte admin créé (id={$id}) : {$email}\n";
    }
} catch (\Throwable $e) {
    fwrite(STDERR, '[ERREUR] ' . $e->getMessage() . "\n");
    exit(1);
}

exit(0);
