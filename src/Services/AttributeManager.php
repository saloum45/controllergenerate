<?php

namespace Saloum45\ControllerGenerate\Services;

use Saloum45\ControllerGenerate\Modifiers\ControllerModifier;
use Saloum45\ControllerGenerate\Modifiers\MigrationModifier;
use Saloum45\ControllerGenerate\Modifiers\ModelModifier;

class AttributeManager
{
    public function __construct(
        protected ModelModifier $modelModifier,
        protected MigrationModifier $migrationModifier,
        protected ControllerModifier $controllerModifier
    ) {}

    /**
     * Applique les modifications directement à partir des chemins fournis.
     */
    public function addAttributePaths(
        string $attribute,
        string $type,
        ?string $modelPath = null,
        ?string $migrationPath = null,
        ?string $controllerPath = null
    ): void {
        // 1. Modèle
        if ($modelPath) {
            $this->modelModifier->addAttribute($modelPath, $attribute);
        }

        // 2. Migration
        if ($migrationPath) {
            $this->migrationModifier->addAttribute($migrationPath, $attribute, $type);
        }

        // 3. Contrôleur
        if ($controllerPath) {
            $this->controllerModifier->addAttribute($controllerPath, $attribute);
        }
    }
}
