<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Illuminate\Support\Str;

class GenerateControllers extends Command
{
    protected $signature = 'generate:controllers';
    protected $description = 'Generate controllers for all models with CRUD methods';
    // 
    public function handle()
    {
        $modelsPath = app_path('Models');
        $controllerPath = app_path('Http/Controllers');

        if (!File::exists($modelsPath)) {
            $this->error("Le dossier Models n'existe pas.");
            return;
        }

        $models = File::files($modelsPath);

        foreach ($models as $model) {
            $modelName = $model->getFilenameWithoutExtension();
            $controllerName = "{$modelName}Controller";
            $controllerFullPath = "$controllerPath/{$controllerName}.php";

            if (File::exists($controllerFullPath)) {
                $this->info("Le contrôleur $controllerName existe déjà, donc il sera ignoré.");
                continue;
            }

            // Générer le contenu du contrôleur avec les méthodes CRUD
            $controllerContent = $this->generateControllerContent($controllerName, $modelName);

            // Créer le fichier de contrôleur avec le contenu généré
            File::put($controllerFullPath, $controllerContent);

            $this->info("Contrôleur $controllerName généré avec succès.");
        }

        $this->info("Tous les contrôleurs ont été générés !");
    }

    protected function generateControllerContent($controllerName, $modelName)
    {
        $modelClass = "App\\Models\\{$modelName}";

        if (!class_exists($modelClass)) {
            $this->error("Le modèle $modelClass n'existe pas.");
            return;
        }

        // Récupère les attributs fillable du modèle
        $fillable = (new ReflectionClass($modelClass))->newInstance()->getFillable();

        // Génère les méthodes CRUD
        $indexMethod = $this->generateIndexMethod($modelName);
        $storeMethod = $this->generateStoreMethod($modelName, $fillable);
        $updateMethod = $this->generateUpdateMethod($modelName, $fillable);
        $destroyMethod = $this->generateDestroyMethod($modelName);
        $showMethod = $this->generateShowMethod($modelName);
        $getFormDetails=$this->generateGetFormDetailsMethod($fillable);
        

        return <<<EOT
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GenerateApiResponse;
use {$modelClass};
use Exception;

class {$controllerName} extends Controller
{
    use GenerateApiResponse;
    
    {$indexMethod}

    {$storeMethod}

    {$updateMethod}

    {$destroyMethod}

    {$showMethod}

    {$getFormDetails}
}
EOT;
    }

    protected function generateIndexMethod($modelName)
    {
        return <<<EOT
    /**
     * Display a listing of the resource.
     *
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function index()
    {
        try {
            \$data = {$modelName}::all();
            return \$this->successResponse(\$data, 'Récupération réussie');
        } catch (Exception \$e) {
            return \$this->errorResponse('Récupération échouée', 500, \$e->getMessage());
        }
    }
EOT;
    }

    protected function generateStoreMethod($modelName, $fillable)
    {
        $modelVar = Str::camel($modelName);
        $fieldsAssignment = implode("\n            ", array_map(
            fn($field) => "\${$modelVar}->$field = \$request->$field;",
            $fillable
        ));

        return <<<EOT
    /**
     * Store a newly created resource in storage.
     *
     * @param  \\Illuminate\\Http\\Request  \$request
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function store(Request \$request)
    {
        try {
            \${$modelVar} = new {$modelName}();
            {$fieldsAssignment}
            if (\${$modelVar}->save()) {
                return \$this->successResponse(\${$modelVar}, 'Récupération réussie');
            }
        } catch (Exception \$e) {
            return \$this->errorResponse('Insertion échouée', 500, \$e->getMessage());
        }
    }
EOT;
    }

    protected function generateUpdateMethod($modelName, $fillable)
    {
        $modelVar = Str::camel($modelName);
        $fieldsAssignment = implode("\n            ", array_map(
            fn($field) => "\${$modelVar}->$field = \$request->$field;",
            $fillable
        ));

        return <<<EOT
    /**
     * Update the specified resource in storage.
     *
     * @param  \\Illuminate\\Http\\Request  \$request
     * @param  int  \$id
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function update(Request \$request, \$id)
    {
        try {
            \${$modelVar} = {$modelName}::findOrFail(\$id);
            {$fieldsAssignment}
            if (\${$modelVar}->save()) {
                return \$this->successResponse(\${$modelVar}, 'Mise à jour réussie');
            }
        } catch (Exception \$e) {
            return \$this->errorResponse('Mise à jour échouée', 500, \$e->getMessage());
        }
    }
EOT;
    }

    protected function generateDestroyMethod($modelName)
    {
        $modelVar = Str::camel($modelName);

        return <<<EOT
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  \$id
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function destroy(\$id)
    {
        try {
            \${$modelVar} = {$modelName}::findOrFail(\$id);
            if (\${$modelVar}->delete()) {
                return \$this->successResponse(\${$modelVar}, 'Suppression réussie');
            }
        } catch (Exception \$e) {
            return \$this->errorResponse('Suppression échouée', 500, \$e->getMessage());
        }
    }
EOT;
    }

    protected function generateShowMethod($modelName)
    {
        $modelVar = Str::camel($modelName);

        return <<<EOT
    /**
     * Display the specified resource.
     *
     * @param  int  \$id
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function show(\$id)
    {
        try {
            \${$modelVar} = {$modelName}::findOrFail(\$id);
             return \$this->successResponse(\${$modelVar}, 'Ressource trouvée');
        } catch (Exception \$e) {
            return \$this->errorResponse('Ressource non trouvée', 404, \$e->getMessage());
        }
    }
EOT;
    }

    protected function generateGetFormDetailsMethod($fillable)
{
    $foreignTables = array_filter($fillable, function ($field) {
        return str_starts_with($field, 'id_');
    });

    $queries = '';
    foreach ($foreignTables as $field) {
        $table = Str::plural(str_replace('id_', '', $field));
        $variable = Str::camel($table);
        $model = Str::studly(Str::singular($table));
        $queries .= "        \$$variable = \App\\Models\\$model::all();\n";
    }

    $responseArray = implode(",\n            ", array_map(function ($field) {
        $table = Str::plural(str_replace('id_', '', $field));
        $variable = Str::camel($table);
        return "'$variable' => \$$variable";
    }, $foreignTables));

    return <<<EOT
    /**
     * Get related form details for foreign keys.
     *
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function getformdetails()
    {
        try {
$queries
            return \$this->successResponse([
                $responseArray
            ], 'Données du formulaire récupérées avec succès');
        } catch (Exception \$e) {
            return \$this->errorResponse('Erreur lors de la récupération des données du formulaire', 500, \$e->getMessage());
        }
    }
EOT;
}

}
