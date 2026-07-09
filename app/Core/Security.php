<?php

declare(strict_types=1);

namespace App\Core;

/**
 * En-têtes de sécurité applicatifs : Content-Security-Policy (avec nonce
 * par requête) et nettoyage des en-têtes qui révèlent l'infrastructure.
 */
final class Security
{
    private static ?string $nonce = null;

    /**
     * Jeton aléatoire unique à la requête, utilisé pour autoriser les
     * scripts inline légitimes sans recourir à 'unsafe-inline'.
     */
    public static function nonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }

        return self::$nonce;
    }

    /**
     * Émet les en-têtes de sécurité de la réponse courante.
     */
    public static function applyHeaders(): void
    {
        header_remove('X-Powered-By');

        $nonce = self::nonce();

        $csp = implode('; ', [
            "default-src 'self'",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com data:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.tiny.cloud",
            "script-src 'self' 'nonce-$nonce' https://cdn.tiny.cloud",
            "connect-src 'self' https://cdn.tiny.cloud",
            "frame-src https://www.youtube-nocookie.com https://www.youtube.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ]);

        header('Content-Security-Policy: ' . $csp);
    }
}
