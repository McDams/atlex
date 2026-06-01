<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\RateLimiter;
use App\Core\Validator;
use App\Models\User;

/**
 * Authentification de l'espace d'administration.
 */
final class AuthController extends Controller
{
    /** Nombre maximal de tentatives échouées avant blocage temporaire. */
    private const MAX_ATTEMPTS = 5;

    /** Durée du blocage (en secondes) une fois le seuil atteint. */
    private const LOCKOUT_SECONDS = 900;

    public function loginForm(): void
    {
        if (Auth::isLoggedIn()) {
            $this->redirect('admin');
        }

        $this->render('admin/login', [
            'title' => 'Connexion — Espace SG',
        ], 'layouts/admin');
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $throttle = new RateLimiter();
        // Clé par IP : empêche un attaquant de verrouiller un compte légitime
        // en visant son email, tout en bloquant le bruteforce depuis une source.
        $throttleKey = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if ($throttle->tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $minutes = (int) ceil($throttle->availableIn($throttleKey) / 60);
            flash('error', "Trop de tentatives de connexion. Réessayez dans {$minutes} minute(s).");
            $this->redirect('admin/login');
        }

        $email = $this->input('email');
        $password = $this->input('password');

        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            $throttle->hit($throttleKey, self::LOCKOUT_SECONDS);
            flash('error', 'Identifiants invalides.');
            $this->redirect('admin/login');
        }

        $user = (new User())->attempt((string) $email, (string) $password);

        if ($user === null) {
            $throttle->hit($throttleKey, self::LOCKOUT_SECONDS);
            flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('admin/login');
        }

        // Connexion réussie : on remet le compteur à zéro.
        $throttle->clear($throttleKey);

        Auth::login($user);
        flash('success', 'Bienvenue, ' . $user['name'] . ' !');
        $this->redirect('admin');
    }

    public function logout(): void
    {
        $this->verifyCsrf();
        Auth::logout();
    }
}
