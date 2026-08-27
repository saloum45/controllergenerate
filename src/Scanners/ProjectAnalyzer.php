<?php


namespace saloum45\controllergenerate\Scanners;

class ProjectAnalyzer
{
    public function __construct(
        protected MigrationScanner $migrationScanner,
        protected ControllerScanner $controllerScanner,
        protected RouteScanner $routeScanner,
    ) {
    }

    /**
     * Analyse complète du projet.
     */
    public function analyze(array $models): array
    {
        $migrations = $this->migrationScanner->scan();

        $controllers = $this->controllerScanner->scan();

        $routes = $this->routeScanner->scan();

        return [
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

            $migration = $this->findMigration(
                $table,
                $migrations
            );

            $controller = $this->findController(
                $modelName,
                $controllers
            );

            $modelRoutes = $this->findRoutes(
                $controller,
                $routes
            );

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
    protected function findMigration(
        string $table,
        array $migrations
    ): ?array {
        foreach ($migrations as $migration) {
            if ($migration['table'] === $table) {
                return $migration;
            }
        }

        return null;
    }

    /**
     * Trouve le controller correspondant au modèle.
     *
     * Vehicule
     *    ↓
     * VehiculeController
     */
    protected function findController(
        string $modelName,
        array $controllers
    ): ?array {
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
    protected function findRoutes(
        ?array $controller,
        array $routes
    ): array {
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
