<?php

/**
 * Configuration de la base de données.
 *
 * Les identifiants sont lus depuis les variables d'environnement (.env).
 * Ce fichier retourne un tableau consommé par App\Core\Database.
 */

return [
    'host'    => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1',
    'port'    => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306',
    'name'    => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'atlex_sport',
    'user'    => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
    'pass'    => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];
