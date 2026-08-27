<?php

namespace saloum45\controllergenerate\Scanners;

class ProjectScanner
{
    public function __construct(
        protected ModelScanner $modelScanner,
        protected ProjectAnalyzer $projectAnalyzer,
    ) {}

    public function scan(): array
    {
        $models = $this->modelScanner->scan();

        return $this->projectAnalyzer->analyze(
            $models
        );
    }
}
