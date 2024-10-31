<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateMigrationsFromModels extends Command
{
    protected $signature = 'generate:migrations-from-models';
    protected $description = 'Generate migrations from existing models based on $fillable attributes';

    public function handle()
    {
        $modelPath = app_path('Models');
        $migrationPath = database_path('migrations');

        if (!File::exists($modelPath)) {
            $this->error("Le dossier des modèles n'existe pas.");
            return;
        }

        // Récupérer tous les modèles dans le dossier Models
        $models = File::files($modelPath);

        foreach ($models as $model) {
            $modelName = $model->getFilenameWithoutExtension();
            $modelClass = "App\\Models\\$modelName";

            // Vérifier que la classe existe et inclut la propriété $fillable
            if (class_exists($modelClass)) {
                $reflection = new ReflectionClass($modelClass);
                if ($reflection->hasProperty('fillable')) {
                    $fillable = $reflection->getProperty('fillable')->getValue(new $modelClass());

                    // Générer la migration
                    $this->generateMigration($modelName, $fillable, $migrationPath);
                    $this->info("Migration pour $modelName générée.");
                }
            }
        }
    }

    protected function generateMigration($modelName, $fillable, $migrationPath)
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));
        $timestamp = date('Y_m_d_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        $migrationFile = $migrationPath . '/' . $fileName;
    
        $columns = '';
        foreach ($fillable as $field) {
            // Vérifie si le champ est une clé étrangère en suivant la convention 'id_xxx'
            if (Str::startsWith($field, 'id_')) {
                $relatedTable = Str::plural(Str::replaceFirst('id_', '', $field));  // Exemple: 'id_privilege' devient 'privileges'
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
            }
        
            public function down()
            {
                Schema::dropIfExists('$tableName');
            }
        };
        EOT;
        
            File::put($migrationFile, $migrationContent);
        }
    
}
