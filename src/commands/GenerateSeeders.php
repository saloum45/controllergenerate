<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateSeeders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:seeders';

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
        $migrationPath = database_path('migrations');
        $seederPath = database_path('seeders');

        if (!File::exists($migrationPath)) {
            $this->error("Le dossier des migrations n'existe pas.");
            return;
        }

        // Récupérer tous les fichiers de migration
        $migrations = File::files($migrationPath);

        foreach ($migrations as $migration) {
            $fileContent = File::get($migration);

            // Récupérer le nom de la table en cherchant "create('table_name')"
            if (preg_match("/Schema::create\('([^']+)'/", $fileContent, $matches)) {
                $tableName = $matches[1];
                // Récupérer les colonnes de la migration
                $fillable = $this->extractColumnsFromMigration($fileContent);

                // Générer le seeder si des colonnes ont été trouvées
                if (!empty($fillable)) {
                    $this->generateSeeder(Str::studly(Str::singular($tableName)), $fillable, $seederPath);
                    $this->info("Seeder pour la table $tableName généré avec succès.");
                } else {
                    $this->warn("Aucune colonne détectée pour la table $tableName.");
                }
            }
        }
    }

    /**
     * Extraire les colonnes d'une migration.
     *
     * @param string $fileContent
     * @return array
     */
    protected function extractColumnsFromMigration($fileContent)
    {
        $fillable = [];
        $lines = explode("\n", $fileContent);

        foreach ($lines as $line) {
            // Rechercher les colonnes avec $table->string('column_name') ou autres types
            if (preg_match("/->(string|integer|bigInteger|text|boolean|date|timestamp)\('([^']+)'/", $line, $matches)) {
                $fillable[] = $matches[2]; // Le nom de la colonne
            }
        }

        return $fillable;
    }

    /**
     * Générer le contenu du seeder.
     */
    protected function generateSeeder($modelName, $fillable, $seederPath)
    {
        $seederName = "{$modelName}Seeder";
        $fileName = "{$seederName}.php";
        $seederFile = $seederPath . '/' . $fileName;
        $this->updateDatabaseSeeder($seederName);
        $fakerAssignments = '';
        foreach ($fillable as $field) {
            // Détection des types de données à partir des conventions de noms
            if (Str::startsWith($field, 'id_')) {
                $fakerAssignments .= "'$field' => random_int(1, 10),\n";
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
            DB::table('$modelName')->insert([
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

    protected function updateDatabaseSeeder($tableName): void
    {
        $databaseSeederPath = database_path('seeders/DatabaseSeeder.php');

        // Lire le contenu du fichier
        $databaseContent = File::get($databaseSeederPath);

        // Le contenu à insérer
        $newLine = "        \$this->call($tableName::class);\n";
        // Trouver l'emplacement de la méthode run
        $pattern = '/public function run\(\): void\s*\{\n/';
        if (preg_match($pattern, $databaseContent, $matches, PREG_OFFSET_CAPTURE)) {
            // Position pour insérer le nouveau code
            $insertPosition = $matches[0][1] + strlen($matches[0][0]);

            // Ajouter le nouveau contenu à la bonne position
            $updatedContent = substr_replace($databaseContent, $newLine, $insertPosition, 0);

            // Vérifier si le contenu existe déjà pour éviter les doublons
            if (str_contains($databaseContent, $newLine)) {
                $this->error("$tableName::class est déjà dans la méthode run().");
            } else {

                // Réécrire le fichier avec le contenu mis à jour
                File::put($databaseSeederPath, $updatedContent);
                $this->info("Le seeder $tableName::class a été ajouté avec succès au fichier DatabaseSeeder.");
            }
        } else {
            $this->error("Impossible de trouver la méthode run() dans DatabaseSeeder.");
        }
    }
}
