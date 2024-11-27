<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Illuminate\Support\Str;


class GenerateSeederFromModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-seeder-from-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelPath = app_path('Models');
        $seederPath = database_path('seeders');
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
                    $this->generateSeeder($modelName, $fillable, $seederPath);
                    $this->info("Migration pour $modelName générée.");
                }
            }
        }
    }

    /**
     * Générer le contenu du seeder.
     */
    protected function generateSeeder($modelName, $fillable, $seederPath)
    {
        $seederName = "{$modelName}Seeder";
        $fileName = "{$seederName}.php";
        $seederFile = $seederPath . '/' . $fileName;

        $fakerAssignments = '';
        foreach ($fillable as $field) {
            // Vérifie si le champ est une clé étrangère
            if (Str::startsWith($field, 'id_')) {
                $fakerAssignments .= "'$field' => random_int(1, 10),\n"; // Exemple : Associe une valeur ID aléatoire
            } elseif (Str::contains($field, 'email')) {
                $fakerAssignments .= "'$field' => \$faker->unique()->safeEmail,\n";
            } elseif (Str::contains($field, 'name')) {
                $fakerAssignments .= "'$field' => \$faker->name,\n";
            } elseif (Str::contains($field, 'phone')) {
                $fakerAssignments .= "'$field' => \$faker->phoneNumber,\n";
            } elseif (Str::contains($field, 'address')) {
                $fakerAssignments .= "'$field' => \$faker->address,\n";
            } elseif (Str::contains($field, 'date')) {
                $fakerAssignments .= "'$field' => \$faker->date,\n";
            } else {
                $fakerAssignments .= "'$field' => \$faker->word,\n";
            }
        }

        $seederContent = <<<EOT
    <?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Faker\Factory as Faker;

    class {$seederName} extends Seeder
    {
        public function run()
        {
            \$faker = Faker::create();

            for (\$i = 0; \$i < 10; \$i++) {
                DB::table((new \App\Models\\$modelName)->getTable())->insert([
                    $fakerAssignments
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    EOT;

        File::put($seederFile, $seederContent);
    }
}
