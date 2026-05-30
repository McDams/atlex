<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Protection CSRF par jeton de session.
 */
final class CSRF
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Génère (ou réutilise) un jeton CSRF stocké en session.
     */
    public static function generateToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Valide un jeton soumis. Lève une exception si invalide.
     */
    public static function validateToken(?string $token): bool
    {
        $stored = $_SESSION[self::SESSION_KEY] ?? null;

        if ($stored === null || $token === null || !hash_equals($stored, $token)) {
            throw new RuntimeException('Jeton CSRF invalide ou expiré.', 419);
        }

        return true;
    }

    /**
     * Valide le jeton présent dans la requête POST courante.
     */
    public static function check(): bool
    {
        return self::validateToken($_POST['_token'] ?? null);
    }
}
