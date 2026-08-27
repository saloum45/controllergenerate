<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use saloum45\controllergenerate\Scanners\ControllerScanner;
use saloum45\controllergenerate\Scanners\MigrationScanner;
use saloum45\controllergenerate\Scanners\ModelScanner;
use saloum45\controllergenerate\Scanners\RouteScanner;

class ScanProjectCommand extends Command
{
    protected $signature = 'generate:scan';

    protected $description = 'Scan the Laravel project';

    public function handle(
        ModelScanner $modelScanner,
        MigrationScanner $migrationScanner,
        ControllerScanner $controllerScanner,
        RouteScanner $routeScanner
    ): int {
        $models = $modelScanner->scan();
        $migrations = $migrationScanner->scan();
        $controllers = $controllerScanner->scan();
        $routes = $routeScanner->scan();

        $data = [
            'models' => $this->analyzeModels(
                $models,
                $migrations,
                $controllers,
                $routes
            ),
            'summary' => [
                'models' => count($models),
                'migrations' => count($migrations),
                'controllers' => count($controllers),
                'routes' => count($routes),
            ],
        ];

        $this->line(
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
        );

        return self::SUCCESS;
    }

    /**
     * Analyse chaque modèle.
     */
    protected function analyzeModels(
        array $models,
        array $migrations,
        array $controllers,
        array $routes
    ): array {
        $result = [];

        foreach ($models as $model) {
            $table = $model['table'];
            $modelName = $model['name'];

            $migration = $this->findMigration($table, $migrations);
            $controller = $this->findController($modelName, $controllers);
            $modelRoutes = $this->findRoutes($controller, $routes);

            $result[] = [
                'name' => $model['name'],
                'class' => $model['class'],
                'path' => $model['path'],
                'table' => $table,
                'fillable' => $model['fillable'],
                'guarded' => $model['guarded'],
                'relations' => $model['relations'],
                'migration' => $migration,
                'controller' => $controller,
                'routes' => $modelRoutes,
                'status' => [
                    'model' => true,
                    'migration' => $migration !== null,
                    'controller' => $controller !== null,
                    'routes' => count($modelRoutes) > 0,
                ],
            ];
        }

        return $result;
    }

    /**
     * Trouve la migration correspondant à une table.
     */
    protected function findMigration(string $table, array $migrations): ?array
    {
        foreach ($migrations as $migration) {
            if ($migration['table'] === $table) {
                return $migration;
            }
        }

        return null;
    }

    /**
     * Trouve le controller correspondant au modèle.
     */
    protected function findController(string $modelName, array $controllers): ?array
    {
        $expectedName = $modelName . 'Controller';

        foreach ($controllers as $controller) {
            if ($controller['name'] === $expectedName) {
                return $controller;
            }
        }

        return null;
    }

    /**
     * Trouve les routes liées au controller.
     */
    protected function findRoutes(?array $controller, array $routes): array
    {
        if ($controller === null) {
            return [];
        }

        $controllerClass = $controller['class'];
        $result = [];

        foreach ($routes as $route) {
            if ($route['controller'] === $controllerClass) {
                $result[] = $route;
            }
        }

        return $result;
    }
}
