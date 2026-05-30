<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modèle clé/valeur pour les paramètres applicatifs.
 *
 * Utilise la table `settings` (clé TEXT UNIQUE, valeur TEXT).
 * Permet de stocker de façon persistente des configurations
 * comme le token API Hostinger ou l'email admin.
 */
final class Setting
{
    private PDO $db;

    /** Nom de la table SQL. */
    private const TABLE = 'settings';

    /** Cache local pour éviter les requêtes répétées dans la même requête HTTP. */
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // Lecture
    // -------------------------------------------------------------------------

    /**
     * Récupère la valeur d'un paramètre par sa clé.
     * Retourne null si la clé n'existe pas.
     */
    public function get(string $key): ?string
    {
        // Cache mémoire
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $stmt = $this->db->prepare(
            'SELECT `value` FROM ' . self::TABLE . ' WHERE `key` = :key LIMIT 1'
        );
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $value = ($row !== false) ? (string) $row['value'] : null;
        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Récupère plusieurs paramètres en une seule requête.
     *
     * @param  string[]                $keys
     * @return array<string, string|null>
     */
    public function getMany(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        $stmt = $this->db->prepare(
            'SELECT `key`, `value` FROM ' . self::TABLE . ' WHERE `key` IN (' . $placeholders . ')'
        );
        $stmt->execute($keys);

        $result = array_fill_keys($keys, null);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[$row['key']] = $row['value'];
            self::$cache[$row['key']] = $row['value'];
        }

        return $result;
    }

    // -------------------------------------------------------------------------
    // Écriture
    // -------------------------------------------------------------------------

    /**
     * Insère ou met à jour un paramètre (INSERT ... ON DUPLICATE KEY UPDATE).
     * Retourne true en cas de succès.
     */
    public function set(string $key, string $value): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ' . self::TABLE . ' (`key`, `value`)
             VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = :value2, `updated_at` = CURRENT_TIMESTAMP'
        );

        $success = $stmt->execute([
            'key'    => $key,
            'value'  => $value,
            'value2' => $value,
        ]);

        if ($success) {
            self::$cache[$key] = $value;
        }

        return $success;
    }

    /**
     * Supprime un paramètre par sa clé.
     */
    public function delete(string $key): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM ' . self::TABLE . ' WHERE `key` = :key'
        );
        $success = $stmt->execute(['key' => $key]);

        if ($success) {
            unset(self::$cache[$key]);
        }

        return $success;
    }

    /**
     * Invalide le cache mémoire (utile lors de tests).
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
