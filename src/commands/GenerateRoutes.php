<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateRoutes extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected $signature = 'generate:routes {model?}';
    protected $description = 'Generate API routes from existing controllers (for one model or all) and install API';

    public function handle()
    {
        $controllerPath = app_path('Http/Controllers');
        $apiRoutesPath = base_path('routes/api.php');

        if (!File::exists($controllerPath)) {
            $this->error("Le dossier des contrôleurs n'existe pas.");
            return;
        }

        $specificModel = $this->argument('model');

        if ($specificModel) {
            // --- MODE 1 : UN SEUL MODÈLE ---
            $controllerName = "{$specificModel}Controller";
            $controllerFile = "$controllerPath/{$controllerName}.php";

            if (!File::exists($controllerFile)) {
                $this->error("Le contrôleur $controllerName n'existe pas.");
                return;
            }

            if ($this->hasControllerRoutes($apiRoutesPath, $controllerName)) {
                $this->warn("Les routes pour {$controllerName} sont déjà définies dans api.php !");
                return;
            }

            $routesToAdd = "";

            // Ajout du 'use' s'il n'existe pas encore
            if (!$this->hasUseImport($apiRoutesPath, $controllerName)) {
                $routesToAdd .= "use App\\Http\\Controllers\\{$controllerName};\n";
            }

            $routesToAdd .= "\n" . $this->generateApiRoutes($specificModel, $controllerName);

            File::append($apiRoutesPath, $routesToAdd);
            $this->info("Routes pour $controllerName ajoutées à la fin de api.php.");

        } else {
            // --- MODE 2 : TOUS LES CONTRÔLEURS (NON DESTRUCTIF) ---
            $controllers = File::files($controllerPath);
            $existingContent = File::exists($apiRoutesPath) ? File::get($apiRoutesPath) : "";

            // Si le fichier n'existe pas ou est vide, initialisation avec les entêtes et routes de base
            if (empty(trim($existingContent))) {
                $routesContent = "<?php \nuse Illuminate\Support\Facades\Route;\n";
                $routesContent .= "use Illuminate\Support\Facades\Artisan;\n";
                $routesContent .= "use Saloum45\ControllerGenerate\Http\Controllers\AttributeController;\n\n";

                $routesContent .= "// La docs du projet via api/generate/docs\n";
                $routesContent .= "Route::get('/generate/docs', function () {\n";
                $routesContent .= "    Artisan::call('generate:scan');\n";
                $routesContent .= "    \$jsonOutput = Artisan::output();\n";
                $routesContent .= "    \$project = json_decode(\$jsonOutput, true);\n";
                $routesContent .= "    return view('controller-generate-views::documentation', compact('project'));\n";
                $routesContent .= "});\n\n";

                $routesContent .= "// Pour l'exécution des commandes de génération depuis l'interface\n";
                $routesContent .= "Route::post('/generator/execute', [AttributeController::class, 'execute'])->name('generator.execute');\n\n";

                File::put($apiRoutesPath, $routesContent);
                $existingContent = File::get($apiRoutesPath);
            }

            $newImports = "";
            $newRoutes = "";
            $addedCount = 0;

            foreach ($controllers as $controller) {
                $controllerName = $controller->getFilenameWithoutExtension();

                if (Str::endsWith($controllerName, 'Controller') && $controllerName !== 'Controller') {
                    $modelName = Str::replaceLast('Controller', '', $controllerName);

                    // Vérifie si les routes du contrôleur manquent
                    if (!$this->hasControllerRoutesContent($existingContent, $controllerName)) {

                        // Import 'use' manquant
                        if (!Str::contains($existingContent, "use App\\Http\\Controllers\\{$controllerName};")) {
                            $newImports .= "use App\\Http\\Controllers\\{$controllerName};\n";
                        }

                        $newRoutes .= $this->generateApiRoutes($modelName, $controllerName);
                        $this->info("Nouvelles routes pour $controllerName ajoutées.");
                        $addedCount++;
                    } else {
                        $this->line("Routes pour $controllerName déjà présentes (ignorées).");
                    }
                }
            }

            if ($addedCount > 0) {
                // Injection des nouveaux imports après "<?php" et des nouvelles routes à la fin
                if (!empty($newImports)) {
                    $existingContent = preg_replace('/^<\?php\s*/i', "<?php\n" . $newImports, $existingContent, 1);
                    File::put($apiRoutesPath, $existingContent);
                }

                if (!empty($newRoutes)) {
                    File::append($apiRoutesPath, "\n" . $newRoutes);
                }

                $this->info("[$addedCount] nouveau(x) contrôleur(s) ajouté(s) à api.php sans tout écraser.");
            } else {
                $this->info("Toutes les routes sont déjà à jour dans api.php.");
            }

            $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇");
        }

        // Exécuter install:api si le fichier n'existe toujours pas
        try {
            if (!File::exists(base_path('routes/api.php'))) {
                $this->call('install:api');
            }
        } catch (\Exception $e) {
            $this->warn("La commande install:api n'existe pas ou a échoué.");
        }
    }

    /**
     * Vérifie la présence des routes réelles pour un contrôleur dans le fichier
     */
    protected function hasControllerRoutes($filePath, $controllerName): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        return $this->hasControllerRoutesContent(File::get($filePath), $controllerName);
    }

    /**
     * Vérifie si le bloc de routes ou l'utilisation du contrôleur dans les méthodes Route:: existe
     */
    protected function hasControllerRoutesContent($content, $controllerName): bool
    {
        $routeCommentSignature = "Routes pour le contrôleur {$controllerName}";
        $controllerUsageSignature = "[{$controllerName}::class";

        return Str::contains($content, $routeCommentSignature) || Str::contains($content, $controllerUsageSignature);
    }

    /**
     * Vérifie la présence de l'import 'use'
     */
    protected function hasUseImport($filePath, $controllerName): bool
    {
        if (!File::exists($filePath)) {
            return false;
        }

        return Str::contains(File::get($filePath), "use App\\Http\\Controllers\\{$controllerName};");
    }

    protected function generateApiRoutes($modelName, $controllerName)
    {
        $routeName = Str::snake(Str::plural($modelName));

        $routes = <<<EOT
// Routes pour le contrôleur {$controllerName}
Route::get('/{$routeName}', [{$controllerName}::class, 'index']);
Route::post('/{$routeName}', [{$controllerName}::class, 'store']);
Route::put('/{$routeName}/{id}', [{$controllerName}::class, 'update']);
Route::delete('/{$routeName}/{id}', [{$controllerName}::class, 'destroy']);
Route::get('/{$routeName}/{id}', [{$controllerName}::class, 'show'])->where('id', '[0-9]+');
Route::get('/{$routeName}/getformdetails', [{$controllerName}::class, 'getformdetails']);
EOT;

        if ($controllerName === 'UserController') {
            $routes .= <<<EOT

Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
EOT;
        }

        return $routes . "\n\n";
    }
}
