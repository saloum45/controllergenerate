<?php

namespace Saloum45\ControllerGenerate\Services;

use Saloum45\ControllerGenerate\Modifiers\ControllerModifier;
use Saloum45\ControllerGenerate\Modifiers\MigrationAlterModifier;
use Saloum45\ControllerGenerate\Modifiers\MigrationModifier;
use Saloum45\ControllerGenerate\Modifiers\ModelModifier;

class AttributeManager
{
    public function __construct(
        protected ModelModifier $modelModifier,
        protected MigrationModifier $migrationModifier,
        protected ControllerModifier $controllerModifier,
        protected MigrationAlterModifier $migrationAlterModifier
    ) {}

    /**
     * Applique les modifications sur la BDD et met à jour les fichiers PHP.
     */
    public function addAttributePaths(
        string $attribute,
        string $type,
        ?string $table = null,
        ?string $modelPath = null,
        ?string $migrationPath = null,
        ?string $controllerPath = null
    ): void {
        // 1. Modifie directement la base de données sans perte de données
        if ($table) {
            $this->migrationAlterModifier->addColumn($table, $attribute, $type);
        }

        // 2. Met à jour le modèle ($fillable)
        if ($modelPath) {
            $this->modelModifier->addAttribute($modelPath, $attribute);
        }

        // 3. Met à jour le fichier de migration d'origine (pour les futurs déploiements)
        if ($migrationPath) {
            $this->migrationModifier->addAttribute($migrationPath, $attribute, $type);
        }

        // 4. Met à jour le contrôleur (store & update)
        if ($controllerPath) {
            $this->controllerModifier->addAttribute($controllerPath, $attribute);
        }
    }
}
