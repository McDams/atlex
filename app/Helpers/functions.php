<?php

/**
 * Helpers globaux disponibles dans toute l'application.
 */

if (!function_exists('e')) {
    /**
     * Échappe une valeur pour un affichage HTML sécurisé.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Construit une URL absolue à partir d'un chemin relatif.
     */
    function url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Raccourci vers un asset public.
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige vers un chemin interne puis arrête l'exécution.
     */
    function redirect(string $path): never
    {
        $location = str_starts_with($path, 'http') ? $path : url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('flash')) {
    /**
     * Définit (avec $msg) ou récupère et consomme (sans $msg) un message flash.
     */
    function flash(string $key, ?string $msg = null): ?string
    {
        if ($msg !== null) {
            $_SESSION['_flash'][$key] = $msg;
            return null;
        }

        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $value;
        }

        return null;
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne valeur de formulaire (repopulation après erreur).
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('set_old')) {
    /**
     * Mémorise les anciennes entrées de formulaire pour le prochain rendu.
     */
    function set_old(array $data): void
    {
        $_SESSION['_old'] = $data;
    }
}

if (!function_exists('clear_old')) {
    function clear_old(): void
    {
        unset($_SESSION['_old']);
    }
}

if (!function_exists('slugify')) {
    /**
     * Transforme un titre en slug URL-safe.
     */
    function slugify(string $text): string
    {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text)
            ?: strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');
        return $text !== '' ? $text : 'item-' . substr(md5((string) microtime(true)), 0, 8);
    }
}

if (!function_exists('format_date_fr')) {
    /**
     * Formate une date au format français lisible.
     */
    function format_date_fr(?string $datetime, bool $withTime = false): string
    {
        if (empty($datetime)) {
            return '';
        }

        $months = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        $ts = strtotime($datetime);
        if ($ts === false) {
            return e($datetime);
        }

        $formatted = (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        if ($withTime) {
            $formatted .= ' à ' . date('H\hi', $ts);
        }

        return $formatted;
    }
}

if (!function_exists('discipline_label')) {
    /**
     * Retourne le libellé lisible d'une discipline.
     */
    function discipline_label(?string $key): string
    {
        $labels = [
            'football'      => 'Football',
            'basketball'    => 'Basketball',
            'handball'      => 'Handball',
            'arts_martiaux' => 'Arts Martiaux',
            'tous'          => 'Toutes disciplines',
        ];

        return $labels[$key] ?? ucfirst((string) $key);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Génère le champ caché CSRF pour les formulaires.
     */
    function csrf_field(): string
    {
        $token = \App\Core\CSRF::generateToken();
        return '<input type="hidden" name="_token" value="' . e($token) . '">';
    }
}

if (!function_exists('method_field')) {
    /**
     * Génère un champ caché pour l'override de méthode HTTP.
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}
