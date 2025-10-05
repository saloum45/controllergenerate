<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use Illuminate\Support\Str;

class GenerateControllers extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻
    protected $signature = 'generate:controllers {model?}';
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

        // Vérifie si un modèle spécifique est demandé
        $specificModel = $this->argument('model');

        if ($specificModel) {
            $this->generateControllerForModel($specificModel, $controllerPath);
        } else {
            // Aucun modèle spécifique → générer pour tous
            $models = File::files($modelsPath);

            foreach ($models as $model) {
                $modelName = $model->getFilenameWithoutExtension();
                $this->generateControllerForModel($modelName, $controllerPath);
            }

            $this->info("Tous les contrôleurs ont été générés !");
            $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻");
        }
    }

    /**
     * Génère le contrôleur d’un modèle donné
     */
    protected function generateControllerForModel($modelName, $controllerPath)
    {
        $controllerName = "{$modelName}Controller";
        $controllerFullPath = "$controllerPath/{$controllerName}.php";

        if (File::exists($controllerFullPath)) {
            $this->warn("Le contrôleur $controllerName existe déjà, il est ignoré.");
            return;
        }

        $controllerContent = $this->generateControllerContent($controllerName, $modelName);

        if ($controllerContent) {
            File::put($controllerFullPath, $controllerContent);
            $this->info("Contrôleur $controllerName généré avec succès.");
        }
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
        $getFormDetails = $this->generateGetFormDetailsMethod($fillable);
        $generateUserAuthMethod = '';
        $hashImport = '';
        if (strtolower($modelName) === 'user') {
            $generateUserAuthMethod = $this->generateUserAuthMethod($modelName);
            $hashImport = "use Illuminate\\Support\\Facades\\Hash;\n";
        }

        return <<<EOT
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\GenerateApiResponse;
use {$modelClass};
{$hashImport}
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

    {$generateUserAuthMethod}
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
            \${$modelVar}->save();
                return \$this->successResponse(\${$modelVar}, 'Récupération réussie');

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
            \${$modelVar}->save();
                return \$this->successResponse(\${$modelVar}, 'Mise à jour réussie');
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
            \${$modelVar}->delete();
                return \$this->successResponse(\${$modelVar}, 'Suppression réussie');
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

    protected function generateUserAuthMethod($modelName)
    {

        # code...

        return <<<EOT
    /**
     * Display the specified resource.
     *
     * @param  int  \$id
     * @return \\Illuminate\\Http\\JsonResponse
     */
    public function login(Request \$request)
    {
        \$request->validate([
            'email' => 'required',
            'password' => 'required|string',
        ]);

        try {

            // \$user = User::where('email', \$request->email)->first();
            \$user = User::where('email', \$request->email)->first();
            if (!\$user || !Hash::check(\$request->password, \$user->password)) {
                return response()->json([
                    'status_code' => 401,
                    'status_message' => 'email ou mot de passe incorrect.'
                ], 401);
            }

            \$token = \$user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status_code' => 200,
                'status_message' => 'Connexion réussie',
                'data' => \$user,
                'token' => \$token
            ], 200);
        } catch (Exception \$e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la connexion',
                'error' => \$e->getMessage()
            ], 500);
        }
    }
    public function logout(Request \$request)
    {
        try {
            \$request->user()->currentAccessToken()->delete();

            return response()->json([
                'status_code' => 200,
                'status_message' => 'Déconnexion réussie'
            ]);
        } catch (Exception \$e) {
            return response()->json([
                'status_code' => 500,
                'status_message' => 'Erreur lors de la déconnexion',
                'error' => \$e->getMessage()
            ], 500);
        }
    }
    EOT;
    }
}
