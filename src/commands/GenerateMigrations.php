<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateMigrations extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected $signature = 'generate:migrations {model?}';
    protected $description = 'Generate migrations from existing models based on $fillable attributes and sort them by foreign key dependencies';

    public function handle()
    {
        $modelPath = app_path('Models');
        $migrationPath = database_path('migrations');

        if (!File::exists($modelPath)) {
            $this->error("Le dossier des modèles n'existe pas.");
            return;
        }

        $modelFiles = File::files($modelPath);
        $modelFillables = [];
        $modelDependencies = [];

        // Étape 1 : collecter les fillables et les dépendances
        foreach ($modelFiles as $modelFile) {
            $modelName = $modelFile->getFilenameWithoutExtension();
            $modelClass = "App\\Models\\$modelName";

            if (!class_exists($modelClass)) {
                continue;
            }

            $reflection = new ReflectionClass($modelClass);
            if ($reflection->hasProperty('fillable')) {
                $instance = new $modelClass();
                $fillable = $reflection->getProperty('fillable')->getValue($instance);

                $modelFillables[$modelName] = $fillable;
                $dependencies = [];

                foreach ($fillable as $field) {
                    if (Str::startsWith($field, 'id_')) {
                        $relatedRaw = Str::replaceFirst('id_', '', $field);
                        $related = Str::studly($relatedRaw);
                        $dependencies[] = $related;
                    }
                }

                $modelDependencies[$modelName] = $dependencies;
            }
        }

        // Étape 2 : filtrer si un modèle précis est fourni
        $targetModel = $this->argument('model');
        if ($targetModel) {
            $targetModel = Str::studly($targetModel);
            if (!isset($modelFillables[$targetModel])) {
                $this->error("Le modèle $targetModel n'existe pas ou n'a pas de fillable.");
                return;
            }

            $modelFillables = [$targetModel => $modelFillables[$targetModel]];
            $modelDependencies = [$targetModel => $modelDependencies[$targetModel] ?? []];
            $this->info("Mode ciblé : génération des migrations uniquement pour $targetModel");
        }

        // Étape 3 : séparer modèles sans id_* et avec id_*
        $modelsWithoutDeps = [];
        $modelsWithDeps = [];

        foreach ($modelFillables as $model => $fillable) {
            $hasForeignKey = false;
            foreach ($fillable as $field) {
                if (Str::startsWith($field, 'id_')) {
                    $hasForeignKey = true;
                    break;
                }
            }

            if ($hasForeignKey) {
                $modelsWithDeps[] = $model;
            } else {
                $modelsWithoutDeps[] = $model;
            }
        }

        // Étape 4 : tri topologique pour modèles avec dépendances
        $sortedWithDeps = [];
        $visited = [];

        $visit = function ($model) use (&$visit, &$modelDependencies, &$visited, &$sortedWithDeps, $modelFillables) {
            if (isset($visited[$model])) {
                return;
            }
            $visited[$model] = true;

            foreach ($modelDependencies[$model] ?? [] as $dependency) {
                if (!isset($modelFillables[$dependency])) {
                    $this->warn("Dépendance manquante : $model (référence $dependency)");
                    continue;
                }
                $visit($dependency);
            }

            $sortedWithDeps[] = $model;
        };

        foreach ($modelsWithDeps as $model) {
            $visit($model);
        }

        // Fusion : d'abord ceux sans dépendances, puis ceux triés avec dépendances
        $sortedModels = array_merge($modelsWithoutDeps, array_diff($sortedWithDeps, $modelsWithoutDeps));

        // Étape 5 : générer les migrations
        foreach ($sortedModels as $modelName) {
            if (!isset($modelFillables[$modelName])) {
                $this->warn("Modèle ignoré (non trouvé) : $modelName");
                continue;
            }

            $fillable = $modelFillables[$modelName];
            $this->generateMigration($modelName, $fillable, $migrationPath);
            $this->info("Migration pour $modelName générée.");
        }

        $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇");
    }

    protected function generateMigration($modelName, $fillable, $migrationPath)
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));

        // Chercher une migration existante pour ce modèle
        $existingMigration = collect(File::files($migrationPath))
            ->first(function ($file) use ($tableName) {
                return str_contains($file->getFilename(), "create_{$tableName}_table");
            });

        if ($existingMigration) {
            $migrationFile = $existingMigration->getRealPath();
            $this->warn("Migration existante trouvée pour $modelName, elle sera écrasée.");
        } else {
            $timestamp = now()->format('Y_m_d_His') . uniqid();
            $fileName = "{$timestamp}_create_{$tableName}_table.php";
            $migrationFile = $migrationPath . '/' . $fileName;
        }

        $columnLines = [];
        foreach ($fillable as $field) {
            if (Str::startsWith($field, 'id_')) {
                $relatedTable = Str::snake(Str::pluralStudly(Str::replaceFirst('id_', '', $field)));
                $columnLines[] = "            \$table->unsignedBigInteger('$field');";
                $columnLines[] = "            \$table->foreign('$field')->references('id')->on('$relatedTable')->onDelete('restrict')->onUpdate('cascade');";
            } else {
                $columnLines[] = "            \$table->string('$field')->nullable();";
            }
        }
        $columns = implode("\n", $columnLines);

        $migrationContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('$tableName', function (Blueprint \$table) {
            \$table->id();
{$columns}
            \$table->timestamps();
        });
EOT;

        if (in_array(strtolower($tableName), ['users', 'user'])) {
            $migrationContent .= <<<EOT


        Schema::create('password_reset_tokens', function (Blueprint \$table) {
            \$table->string('email')->primary();
            \$table->string('token');
            \$table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint \$table) {
            \$table->string('id')->primary();
            \$table->foreignId('user_id')->nullable()->index();
            \$table->string('ip_address', 45)->nullable();
            \$table->text('user_agent')->nullable();
            \$table->longText('payload');
            \$table->integer('last_activity')->index();
        });
EOT;
        }

        $migrationContent .= <<<EOT

    }

    public function down()
    {
        Schema::dropIfExists('$tableName');
EOT;

        if (in_array(strtolower($tableName), ['users', 'user'])) {
            $migrationContent .= <<<EOT

        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
EOT;
        }

        $migrationContent .= <<<EOT

    }
};
EOT;

        File::put($migrationFile, $migrationContent);
    }
}