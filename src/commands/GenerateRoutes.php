<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateRoutes extends Command
{
    protected $signature = 'generate:routes';
    protected $description = 'Generate API routes from existing controllers and install API';

    public function handle()
    {
        $controllerPath = app_path('Http/Controllers');
        $apiRoutesPath = base_path('routes/api.php');
        $routesContent = '';

        // Vérifier si le dossier des contrôleurs existe
        if (!File::exists($controllerPath)) {
            $this->error("Le dossier des contrôleurs n'existe pas.");
            return;
        }

        // Lister tous les contrôleurs dans le dossier Http/Controllers
        $controllers = File::files($controllerPath);

        $routesContent="<?php \nuse Illuminate\Support\Facades\Route;\n";
        foreach ($controllers as $controller) {
            $controllerName = $controller->getFilenameWithoutExtension();
            // S'assurer que c'est bien un contrôleur
            if (Str::endsWith($controllerName, 'Controller')&& $controllerName !== 'Controller') {
                $modelName = Str::replaceLast('Controller', '', $controllerName);
                $routesContent .= "use App\Http\Controllers\\".$controllerName.";\n";
            }
        }
        $routesContent .= "\n";
        foreach ($controllers as $controller) {
            $controllerName = $controller->getFilenameWithoutExtension();
            // S'assurer que c'est bien un contrôleur
            if (Str::endsWith($controllerName, 'Controller')&& $controllerName !== 'Controller') {
                $modelName = Str::replaceLast('Controller', '', $controllerName);
                $routesContent .= $this->generateApiRoutes($modelName, $controllerName);
                $this->info("Routes pour $controllerName générées.");
            }
        }

        // Ajouter les routes au fichier api.php
        if (!empty($routesContent)) {
            File::put($apiRoutesPath, "\n" . $routesContent);
            $this->info("Les routes API ont été ajoutées au fichier api.php.");
        }

        // Installer l'API via la commande artisan
        $this->call('install:api');
    }

    protected function generateApiRoutes($modelName, $controllerName)
    {
        $routeName = Str::kebab(Str::plural($modelName));

        return <<<EOT
// Routes pour le contrôleur {$controllerName}
Route::get('/{$routeName}', [$controllerName::class,'index']);
Route::post('/{$routeName}', [$controllerName::class,'store']);
Route::put('/{$routeName}/{id}', [$controllerName::class,'update']);
Route::delete('/{$routeName}/{id}', [$controllerName::class,'destroy']);
Route::get('/{$routeName}/{id}', [$controllerName::class, 'show'])->where('id', '[0-9]+');
Route::get('/{$routeName}/getformdetails', [$controllerName::class, 'getformdetails']);
\n
EOT;
    }
}
