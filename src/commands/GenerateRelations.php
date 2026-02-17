<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateRelations extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected $signature = 'generate:relations {model?}';
    protected $description = 'Scan models and generate belongsTo and hasMany relations';

    public function handle()
    {
        $modelsPath = app_path('Models');
        $modelFiles = File::allFiles($modelsPath);

        $models = [];

        // Étape 1 : Charger tous les modèles existants
        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            if (!class_exists($className)) continue;

            $instance = new $className;
            $fillable = $instance->getFillable();
            $models[$className] = [
                'fillable' => $fillable,
                'file' => $file->getRealPath(),
            ];
        }

        // Étape 2 : Filtrer si un modèle précis est fourni
        $targetModel = $this->argument('model');
        if ($targetModel) {
            $targetClass = "App\\Models\\" . Str::studly($targetModel);
            if (!isset($models[$targetClass])) {
                $this->error("Le modèle $targetClass n'existe pas ou n'a pas de fillable.");
                return;
            }
            $models = [$targetClass => $models[$targetClass]];
            $this->info("Mode ciblé : génération des relations uniquement pour $targetClass");
        }

        // Étape 3 : Générer belongsTo pour chaque modèle ciblé
        foreach ($models as $modelClass => $data) {
            $relationsCode = "";

            foreach ($data['fillable'] as $field) {
                if (str_starts_with($field, 'id_')) {
                    $relatedModelName = $this->guessModelNameFromForeignKey($field);
                    $relatedClass = "App\\Models\\$relatedModelName";

                    if (class_exists($relatedClass)) {
                        // $methodName = lcfirst($relatedModelName);
                        $methodName = Str::snake($relatedModelName);

                        // vérifier si la relation existe déjà
                        $existingCode = file_get_contents($data['file']);
                        if (!Str::contains($existingCode, "function $methodName(")) {
                            $relationsCode .= "\n    public function $methodName()\n    {\n        return \$this->belongsTo($relatedModelName::class, '$field');\n    }\n";
                        }
                    }
                }
            }

            $this->injectRelations($data['file'], $relationsCode);
        }

        // Étape 4 : Générer hasMany pour les modèles parent
        foreach ($models as $parentModel => $parentData) {
            $parentClassName = class_basename($parentModel);

            foreach ($modelFiles as $file) {
                $childClass = 'App\\Models\\' . $file->getFilenameWithoutExtension();
                if (!class_exists($childClass)) continue;

                $childInstance = new $childClass;
                $childFillable = $childInstance->getFillable();

                foreach ($childFillable as $field) {
                    if (str_starts_with($field, 'id_')) {
                        $guessedModel = $this->guessModelNameFromForeignKey($field);
                        if ($guessedModel === $parentClassName) {
                            // $relationName = lcfirst(Str::pluralStudly(class_basename($childClass)));
                            $relationName = Str::snake(Str::pluralStudly(class_basename($childClass)));
                            $existingCode = file_get_contents($parentData['file']);

                            if (!Str::contains($existingCode, "function $relationName(")) {
                                $relationCode = "\n    public function $relationName()\n    {\n        return \$this->hasMany(" . class_basename($childClass) . "::class, '$field');\n    }\n";
                                $this->injectRelations($parentData['file'], $relationCode);
                                $this->info("Relation hasMany ajoutée à $parentClassName : $relationName()");
                            }
                        }
                    }
                }
            }
        }

        $this->info("Relations générées avec succès !");
        $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇");
    }

    protected function injectRelations($filePath, $relationsCode)
    {
        if (empty(trim($relationsCode))) return;

        $content = file_get_contents($filePath);

        if (str_contains($content, 'function ')) {
            // ajouter avant la dernière accolade fermante
            $content = preg_replace('/}\s*$/', "$relationsCode\n}", $content);
        } else {
            // injecter en fin de classe
            $content = str_replace('}', "$relationsCode\n}", $content);
        }

        file_put_contents($filePath, $content);
    }

    protected function guessModelNameFromForeignKey(string $foreignKey): string
    {
        $base = preg_replace('/^id_/', '', $foreignKey);
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $base)));
    }
}
