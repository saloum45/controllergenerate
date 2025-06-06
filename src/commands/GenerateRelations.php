<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class GenerateRelations extends Command
{
    protected $signature = 'generate:relations';
    protected $description = 'Scan models and generate belongsTo and hasMany relations';

    public function handle()
    {
        $modelsPath = app_path('Models');
        $modelFiles = File::allFiles($modelsPath);

        $models = [];

        // Étape 1 : Charger tous les modèles
        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            if (!class_exists($className)) continue;

            $instance = new $className;
            $fillable = $instance->getFillable();
            $models[$className] = ['fillable' => $fillable, 'file' => $file->getRealPath()];
        }

        // Étape 2 : Générer belongsTo
        foreach ($models as $modelClass => $data) {
            $relationsCode = "";

            foreach ($data['fillable'] as $field) {
                if (str_starts_with($field, 'id_')) {
                    $relatedModelName = $this->guessModelNameFromForeignKey($field);

                    $relatedClass = "App\\Models\\$relatedModelName";

                    if (class_exists($relatedClass)) {
                        $methodName = lcfirst($relatedModelName);
                        $relationsCode .= "\n    public function $methodName()\n    {\n        return \$this->belongsTo($relatedModelName::class, '$field');\n    }\n";
                    }
                }
            }

            // Injecter le code dans le modèle
            $this->injectRelations($data['file'], $relationsCode);
        }

        // Étape 3 : Générer hasMany
        foreach ($models as $parentModel => $parentData) {
            $parentClassName = class_basename($parentModel);

            foreach ($models as $childModel => $childData) {
                foreach ($childData['fillable'] as $field) {
                    if (str_starts_with($field, 'id_')) {
                        $guessedModel = $this->guessModelNameFromForeignKey($field);
                        if ($guessedModel === $parentClassName) {
                            $relationName = lcfirst(Str::pluralStudly(class_basename($childModel)));

                            // S'assurer qu'on n'a pas déjà cette méthode dans le modèle parent
                            $existingCode = file_get_contents($parentData['file']);
                            if (!Str::contains($existingCode, "function $relationName(")) {
                                $relationCode = "\n    public function $relationName()\n    {\n        return \$this->hasMany(" . class_basename($childModel) . "::class, '$field');\n    }\n";
                                $this->injectRelations($parentData['file'], $relationCode);
                                $this->info("Relation hasMany ajoutée à $parentClassName : $relationName()");
                            }
                        }
                    }
                }
            }
        }


        $this->info("Relations générées avec succès !");
    }

    protected function injectRelations($filePath, $relationsCode)
    {
        $content = file_get_contents($filePath);

        if (str_contains($content, 'function ')) {
            // Ajouter avant la dernière accolade
            $content = preg_replace('/}\s*$/', "$relationsCode\n}", $content);
        } else {
            // Pas encore de méthode, injecter en fin de classe
            $content = str_replace('}', "$relationsCode\n}", $content);
        }

        file_put_contents($filePath, $content);
    }

    protected function guessModelNameFromForeignKey(string $foreignKey): string
    {
        // Supprimer le préfixe id_
        $base = preg_replace('/^id_/', '', $foreignKey);

        // Transformer snake_case en StudlyCase
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $base)));
    }
}
