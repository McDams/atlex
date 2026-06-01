<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Limiteur de débit basé sur le système de fichiers.
 *
 * Compte les tentatives par clé (ex. « login:IP ») sur une fenêtre glissante.
 * Stockage dans storage/cache/ratelimit pour survivre à la suppression des
 * cookies de session (contrairement à un compteur en session).
 */
final class RateLimiter
{
    private string $dir;

    public function __construct(?string $dir = null)
    {
        $this->dir = rtrim($dir ?? ROOT . '/storage/cache/ratelimit', '/');
    }

    /**
     * Indique si le nombre maximal de tentatives est atteint pour cette clé.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }

    /**
     * Incrémente le compteur de la clé et retourne le nouveau total.
     * La fenêtre d'expiration est fixée lors de la première tentative.
     */
    public function hit(string $key, int $decaySeconds): int
    {
        $record = $this->read($key);
        $expires = $record['expires'] > 0 ? $record['expires'] : time() + $decaySeconds;
        $count   = $record['count'] + 1;

        $this->write($key, ['count' => $count, 'expires' => $expires]);

        return $count;
    }

    /**
     * Nombre de tentatives actuellement comptabilisées pour la clé.
     */
    public function attempts(string $key): int
    {
        return $this->read($key)['count'];
    }

    /**
     * Secondes restantes avant la réinitialisation du compteur.
     */
    public function availableIn(string $key): int
    {
        return max(0, $this->read($key)['expires'] - time());
    }

    /**
     * Réinitialise le compteur (ex. après une connexion réussie).
     */
    public function clear(string $key): void
    {
        $file = $this->path($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function path(string $key): string
    {
        return $this->dir . '/' . sha1($key) . '.json';
    }

    /**
     * @return array{count:int,expires:int}
     */
    private function read(string $key): array
    {
        $file = $this->path($key);
        if (!is_file($file)) {
            return ['count' => 0, 'expires' => 0];
        }

        $data = json_decode((string) file_get_contents($file), true);

        // Fenêtre expirée ou fichier corrompu : on repart à zéro.
        if (!is_array($data) || (int) ($data['expires'] ?? 0) < time()) {
            return ['count' => 0, 'expires' => 0];
        }

        return ['count' => (int) $data['count'], 'expires' => (int) $data['expires']];
    }

    /**
     * @param array{count:int,expires:int} $data
     */
    private function write(string $key, array $data): void
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0o755, true);
        }

        file_put_contents($this->path($key), json_encode($data), LOCK_EX);
    }
}
