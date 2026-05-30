<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Middleware et helpers d'authentification de l'espace admin.
 */
final class Auth
{
    /**
     * Indique si un administrateur est connecté.
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_role']);
    }

    /**
     * Exige une session admin valide, sinon redirige vers la connexion.
     */
    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            flash('error', 'Veuillez vous connecter pour accéder à cet espace.');
            redirect('admin/login');
        }
    }

    /**
     * Ouvre une session admin pour l'utilisateur fourni.
     *
     * @param array<string,mixed> $user
     */
    public static function login(array $user): void
    {
        Session::regenerate();
        $_SESSION['admin_id']    = (int) $user['id'];
        $_SESSION['admin_name']  = $user['name'] ?? 'Administrateur';
        $_SESSION['admin_email'] = $user['email'] ?? '';
        $_SESSION['admin_role']  = $user['role'] ?? 'editor';
    }

    /**
     * Ferme la session admin et redirige vers la connexion.
     */
    public static function logout(): never
    {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_email'],
            $_SESSION['admin_role']
        );

        Session::regenerate();
        redirect('admin/login');
    }

    public static function id(): ?int
    {
        return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
    }

    public static function user(): array
    {
        return [
            'id'    => $_SESSION['admin_id'] ?? null,
            'name'  => $_SESSION['admin_name'] ?? null,
            'email' => $_SESSION['admin_email'] ?? null,
            'role'  => $_SESSION['admin_role'] ?? null,
        ];
    }
}
