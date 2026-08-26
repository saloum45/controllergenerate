<?php

namespace app\Console\Commands;

class ProjectScanner
{
    public function __construct(
        protected ModelScanner $modelScanner,
        protected MigrationScanner $migrationScanner,
        protected ControllerScanner $controllerScanner,
        protected RouteScanner $routeScanner,
        protected ProjectAnalyzer $projectAnalyzer,
    ) {
    }

    public function scan(): array
    {
        $models = $this->modelScanner->scan();

        return $this->projectAnalyzer->analyze(
            $models
        );
    }
}
