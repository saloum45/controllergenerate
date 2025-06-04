<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateMigrations extends Command
{
    protected $signature = 'generate:migrations';
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

            if (class_exists($modelClass)) {
                $reflection = new ReflectionClass($modelClass);
                if ($reflection->hasProperty('fillable')) {
                    $instance = new $modelClass();
                    $fillable = $reflection->getProperty('fillable')->getValue($instance);

                    $modelFillables[$modelName] = $fillable;
                    $dependencies = [];

                    foreach ($fillable as $field) {
                        if (Str::startsWith($field, 'id_')) {
                            // Correction casse pour uniformiser
                            $relatedRaw = Str::replaceFirst('id_', '', $field);
                            $related = Str::studly($relatedRaw);
                            $dependencies[] = $related;
                        }
                    }

                    $modelDependencies[$modelName] = $dependencies;
                }
            }
        }

        // Étape 2 : séparer modèles sans id_* et avec id_*
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

        // Tri topologique pour les modèles avec dépendances
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

        // Étape 3 : générer les migrations dans le bon ordre
        foreach ($sortedModels as $modelName) {
            if (!isset($modelFillables[$modelName])) {
                $this->warn("Modèle ignoré (non trouvé) : $modelName");
                continue;
            }

            $fillable = $modelFillables[$modelName];
            $this->generateMigration($modelName, $fillable, $migrationPath);
            $this->info("Migration pour $modelName générée.");
        }
    }



    protected function topologicalSort(array $graph)
    {
        $visited = [];
        $temp = [];
        $sorted = [];

        $visit = function ($node) use (&$visit, &$visited, &$temp, &$sorted, $graph) {
            if (isset($temp[$node])) return false; // cycle détecté
            if (!isset($visited[$node])) {
                $temp[$node] = true;
                foreach ($graph[$node] ?? [] as $neighbor) {
                    if (!$visit($neighbor)) return false;
                }
                $visited[$node] = true;
                unset($temp[$node]);
                $sorted[] = $node;
            }
            return true;
        };

        foreach (array_keys($graph) as $node) {
            if (!$visit($node)) return null; // cycle détecté
        }

        return array_reverse($sorted); // ordre de dépendance
    }

    protected function generateMigration($modelName, $fillable, $migrationPath)
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));
        $timestamp = now()->format('Y_m_d_His') . uniqid();
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        $migrationFile = $migrationPath . '/' . $fileName;

        $columns = '';
        foreach ($fillable as $field) {
            if (Str::startsWith($field, 'id_')) {
                $relatedTable = Str::snake(Str::pluralStudly(Str::replaceFirst('id_', '', $field)));
                $columns .= "\$table->unsignedBigInteger('$field');\n";
                $columns .= "\$table->foreign('$field')->references('id')->on('$relatedTable')->onDelete('cascade')->onUpdate('cascade');\n";
            } else {
                $columns .= "\$table->string('$field');\n";
            }
        }

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
            $columns
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
