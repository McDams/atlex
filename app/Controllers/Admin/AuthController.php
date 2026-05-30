<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;

/**
 * Authentification de l'espace d'administration.
 */
final class AuthController extends Controller
{
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

        $email = $this->input('email');
        $password = $this->input('password');

        $validator = new Validator(['email' => $email, 'password' => $password]);
        $validator->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            flash('error', 'Identifiants invalides.');
            $this->redirect('admin/login');
        }

        $user = (new User())->attempt((string) $email, (string) $password);

        if ($user === null) {
            flash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('admin/login');
        }

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
