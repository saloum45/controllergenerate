<?php

namespace Saloum45\ControllerGenerate\Modifiers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MigrationAlterModifier
{
    /**
     * Ajoute une colonne directement dans la base de données sans supprimer la table existante.
     */
    public function addColumn(string $table, string $attribute, string $type): void
    {
        if (! Schema::hasTable($table)) {
            // throw new RuntimeException("La table '{$table}' n'existe pas dans la base de données.");
            Log::info("La table '{$table}' n'existe pas dans la base de données.");
            return;
        }

        // Si la colonne existe déjà, on ne réexécute pas
        if (Schema::hasColumn($table, $attribute)) {
            Log::info("La colonne '{$attribute}' existe déjà dans la table '{$table}'.");
            return;
        }

        // Altération de la table
        Schema::table($table, function (Blueprint $tableGroup) use ($attribute, $type) {
            if (method_exists($tableGroup, $type)) {
                $tableGroup->{$type}($attribute)->nullable();
            } else {
                $tableGroup->string($attribute)->nullable();
            }
        });

        // Validation immédiate
        if (Schema::hasColumn($table, $attribute)) {
            Log::info("Succès : La colonne '{$attribute}' a été créée dans la table '{$table}'.");
        } else {
            Log::error("Échec : Impossible de créer la colonne '{$attribute}' dans '{$table}'.");
            throw new RuntimeException("L'ajout de la colonne '{$attribute}' en base de données a échoué.");
        }
    }
}
