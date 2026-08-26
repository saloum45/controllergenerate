<?php


namespace App\Console\Commands;

use Illuminate\Support\Facades\Route;

class RouteScanner
{
    /**
     * Scan toutes les routes enregistrées par Laravel.
     */
    public function scan(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $action = $route->getActionName();

            /*
             * Ignorer les routes internes Laravel.
             */
            if ($this->shouldIgnore($route, $action)) {
                continue;
            }

            $routes[] = [
                'method' => $this->getMethod($route),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $action,
                'controller' => $this->extractController($action),
                'function' => $this->extractFunction($action),
                'middleware' => $route->middleware(),
            ];
        }

        return $routes;
    }

    /**
     * Récupère la méthode HTTP.
     */
    protected function getMethod($route): string
    {
        $methods = $route->methods();

        /*
         * Laravel peut retourner plusieurs méthodes.
         *
         * Exemple :
         *
         * ['GET', 'HEAD']
         *
         * On retire HEAD car il est généralement
         * automatiquement associé à GET.
         */
        $methods = array_values(
            array_diff($methods, ['HEAD'])
        );

        return implode('|', $methods);
    }

    /**
     * Vérifie si une route doit être ignorée.
     */
    protected function shouldIgnore(
        $route,
        string $action
    ): bool {
        /*
         * Routes générées par Laravel.
         */
        if (
            str_starts_with(
                $action,
                'Illuminate\\'
            )
        ) {
            return true;
        }

        /*
         * Route Closure.
         *
         * Pour l'instant on ne les traite pas.
         */
        if ($action === 'Closure') {
            return true;
        }

        return false;
    }

    /**
     * Récupère le nom du controller.
     */
    protected function extractController(
        string $action
    ): ?string {
        if (
            $action === 'Closure'
            || !str_contains($action, '@')
        ) {
            return null;
        }

        [$controller] = explode('@', $action);

        return $controller;
    }

    /**
     * Récupère la méthode du controller.
     */
    protected function extractFunction(
        string $action
    ): ?string {
        if (
            $action === 'Closure'
            || !str_contains($action, '@')
        ) {
            return null;
        }

        [, $function] = explode('@', $action);

        return $function;
    }
}
