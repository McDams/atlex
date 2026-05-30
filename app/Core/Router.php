<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Routeur RESTful maison.
 *
 * - Parse l'URI après suppression du chemin de base
 * - Supporte les segments dynamiques {param}
 * - Supporte l'override de méthode via _method
 * - Protège automatiquement le préfixe /admin (sauf /admin/login)
 */
final class Router
{
    /** @var array<int,array{method:string,pattern:string,regex:string,params:array<int,string>,handler:string}> */
    private array $routes = [];

    /**
     * Enregistre une route.
     */
    public function add(string $method, string $pattern, string $handler): void
    {
        $pattern = '/' . trim($pattern, '/');
        $params = [];

        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            static function (array $m) use (&$params): string {
                $params[] = $m[1];
                return '([^/]+)';
            },
            $pattern
        );

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, string $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function put(string $pattern, string $handler): void
    {
        $this->add('PUT', $pattern, $handler);
    }

    public function delete(string $pattern, string $handler): void
    {
        $this->add('DELETE', $pattern, $handler);
    }

    /**
     * Résout la requête courante et exécute le contrôleur correspondant.
     */
    public function dispatch(string $uri, string $method): void
    {
        $method = $this->resolveMethod($method);
        $path = $this->normalizePath($uri);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            array_shift($matches);
            $params = array_combine($route['params'], $matches) ?: [];

            $this->guardAdmin($path);

            try {
                $this->invoke($route['handler'], $params);
            } catch (Throwable $e) {
                $this->handleError($e);
            }

            return;
        }

        $this->notFound();
    }

    /**
     * Détermine la méthode HTTP effective (override _method).
     */
    private function resolveMethod(string $method): string
    {
        $method = strtoupper($method);

        if ($method === 'POST' && !empty($_POST['_method'])) {
            $override = strtoupper((string) $_POST['_method']);
            if (in_array($override, ['PUT', 'DELETE', 'PATCH'], true)) {
                return $override;
            }
        }

        return $method;
    }

    /**
     * Normalise le chemin en retirant le query string et le base path.
     */
    private function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && $basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }

    /**
     * Vérifie l'authentification pour les routes admin protégées.
     */
    private function guardAdmin(string $path): void
    {
        if (!str_starts_with($path, '/admin')) {
            return;
        }

        // Les routes de connexion restent publiques.
        if ($path === '/admin/login' || $path === '/admin/logout') {
            return;
        }

        Auth::requireAuth();
    }

    /**
     * Instancie le contrôleur et appelle l'action ciblée.
     *
     * @param array<string,string> $params
     */
    private function invoke(string $handler, array $params): void
    {
        [$controllerName, $action] = explode('@', $handler);

        $class = 'App\\Controllers\\' . str_replace('/', '\\', $controllerName);

        if (!class_exists($class)) {
            throw new \RuntimeException("Contrôleur introuvable : $class");
        }

        $controller = new $class();

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action introuvable : $class@$action");
        }

        $controller->{$action}(...array_values($params));
    }

    /**
     * Affiche la page 404.
     */
    private function notFound(): void
    {
        http_response_code(404);
        $file = VIEWS_PATH . '/errors/404.php';
        if (is_file($file)) {
            require $file;
        } else {
            echo '404 — Page introuvable';
        }
    }

    /**
     * Gère une erreur applicative (500 ou code dédié).
     */
    private function handleError(Throwable $e): void
    {
        error_log('[Router] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

        $code = $e->getCode();
        if ($code === 419) {
            http_response_code(419);
            echo 'Session expirée. Veuillez réessayer.';
            return;
        }

        http_response_code(500);

        if (defined('APP_DEBUG') && APP_DEBUG) {
            echo '<pre style="padding:2rem;font-family:monospace">';
            echo e($e->getMessage()) . "\n\n";
            echo e($e->getTraceAsString());
            echo '</pre>';
            return;
        }

        $file = VIEWS_PATH . '/errors/500.php';
        if (is_file($file)) {
            require $file;
        } else {
            echo '500 — Erreur interne';
        }
    }
}
