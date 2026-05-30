<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton PDO pour l'accès à la base de données MySQL.
 */
final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * Retourne l'instance PDO unique partagée.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $config = require ROOT . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            self::$instance = new PDO($dsn, $config['user'], $config['pass'], $options);
        } catch (PDOException $e) {
            if (defined('APP_DEBUG') && APP_DEBUG) {
                throw new RuntimeException('Connexion BDD échouée : ' . $e->getMessage(), (int) $e->getCode());
            }

            error_log('[DB] ' . $e->getMessage());
            throw new RuntimeException('Service indisponible.', 500);
        }

        return self::$instance;
    }

    /**
     * Réinitialise l'instance (utile pour les tests).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
