<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateRoutes extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻
    protected $signature = 'generate:routes {model?}';
    protected $description = 'Generate API routes from existing controllers (for one model or all) and install API';

    public function handle()
    {
        $controllerPath = app_path('Http/Controllers');
        $apiRoutesPath = base_path('routes/api.php');
        $routesContent = "<?php \nuse Illuminate\Support\Facades\Route;\n";

        if (!File::exists($controllerPath)) {
            $this->error("Le dossier des contrôleurs n'existe pas.");
            return;
        }

        $specificModel = $this->argument('model');

        if ($specificModel) {
            // Générer uniquement pour ce modèle et AJOUTER à la fin du fichier api.php
            $controllerName = "{$specificModel}Controller";
            $controllerFile = "$controllerPath/{$controllerName}.php";

            if (!File::exists($controllerFile)) {
                $this->error("Le contrôleur $controllerName n'existe pas.");
                return;
            }

            $routes = "use App\\Http\\Controllers\\{$controllerName};\n\n";
            $routes .= $this->generateApiRoutes($specificModel, $controllerName);

            // Append au lieu de remplacer
            File::append($apiRoutesPath, "\n" . $routes);

            $this->info("Routes pour $controllerName ajoutées à la fin de api.php.");
        } else {
            // Générer pour TOUS les contrôleurs (remplace le fichier)
            $controllers = File::files($controllerPath);

            foreach ($controllers as $controller) {
                $controllerName = $controller->getFilenameWithoutExtension();

                if (Str::endsWith($controllerName, 'Controller') && $controllerName !== 'Controller') {
                    $modelName = Str::replaceLast('Controller', '', $controllerName);
                    $routesContent .= "use App\\Http\\Controllers\\{$controllerName};\n";
                }
            }

            $routesContent .= "\n";

            foreach ($controllers as $controller) {
                $controllerName = $controller->getFilenameWithoutExtension();

                if (Str::endsWith($controllerName, 'Controller') && $controllerName !== 'Controller') {
                    $modelName = Str::replaceLast('Controller', '', $controllerName);
                    $routesContent .= $this->generateApiRoutes($modelName, $controllerName);
                    $this->info("Routes pour $controllerName générées.");
                }
            }

            File::put($apiRoutesPath, $routesContent);
            $this->info("Toutes les routes API ont été régénérées dans api.php.");
            $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻");
        }

        // Exécuter install:api si elle existe
        try {
            // $this->call('install:api');
            if (!File::exists(base_path('routes/api.php'))) {
                $this->call('install:api');
            }
        } catch (\Exception $e) {
            $this->warn("La commande install:api n'existe pas ou a échoué.");
        }
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
