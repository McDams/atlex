<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Contrôleur de base : rendu de vues, redirections, helpers JSON.
 */
abstract class Controller
{
    /**
     * Rend une vue dans un layout donné.
     *
     * @param string              $view   chemin relatif sans extension (ex: home/index)
     * @param array<string,mixed> $data   variables exposées à la vue
     * @param string              $layout layout enveloppant (layouts/main par défaut)
     */
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $content = $this->view($view, $data);

        $title     = $data['title'] ?? APP_NAME;
        $bodyClass = $data['bodyClass'] ?? '';

        extract($data, EXTR_SKIP);

        require VIEWS_PATH . '/' . $layout . '.php';
    }

    /**
     * Capture le rendu d'une vue et retourne le HTML produit.
     *
     * @param array<string,mixed> $data
     */
    protected function view(string $view, array $data = []): string
    {
        $file = VIEWS_PATH . '/' . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("Vue introuvable : $view");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    /**
     * Redirige vers un chemin interne.
     */
    protected function redirect(string $path): never
    {
        redirect($path);
    }

    /**
     * Émet une réponse JSON et arrête l'exécution.
     *
     * @param mixed $payload
     */
    protected function json(mixed $payload, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Récupère et nettoie une valeur de l'input POST/GET.
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Vérifie le jeton CSRF de la requête courante.
     */
    protected function verifyCsrf(): void
    {
        CSRF::check();
    }
}
