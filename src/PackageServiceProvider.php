<?php

namespace saloum45\controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrer la commande GenerateControllers
        $this->app->singleton(\App\Console\Commands\GenerateControllers::class, function () {
            return new \App\Console\Commands\GenerateControllers();
        });

        // Enregistrer la commande GenerateRoutesFromControllers
        $this->app->singleton(\App\Console\Commands\GenerateRoutesFromControllers::class, function () {
            return new \App\Console\Commands\GenerateRoutesFromControllers();
        });

        $this->commands([
            \App\Console\Commands\GenerateControllers::class,
            \App\Console\Commands\GenerateRoutesFromControllers::class,
        ]);
    }

    public function boot()
    {
        // Chemin source de la commande GenerateControllers dans le package
        $sourceControllers = __DIR__.'/Commands/GenerateControllers.php';
        // Chemin de destination pour la commande dans le dossier app/Console/Commands
        $destinationControllers = app_path('Console/Commands/GenerateControllers.php');

        // Si le fichier de commande GenerateControllers n'existe pas, le créer et y copier le contenu
        if (!File::exists($destinationControllers)) {
            File::ensureDirectoryExists(app_path('Console/Commands'));
            $commandContent = File::get($sourceControllers);
            File::put($destinationControllers, $commandContent);
        }

        // Chemin source de la commande GenerateRoutesFromControllers dans le package
        $sourceRoutes = __DIR__.'/Commands/GenerateRoutesFromControllers.php';
        // Chemin de destination pour la commande dans le dossier app/Console/Commands
        $destinationRoutes = app_path('Console/Commands/GenerateRoutesFromControllers.php');

        // Si le fichier de commande GenerateRoutesFromControllers n'existe pas, le créer et y copier le contenu
        if (!File::exists($destinationRoutes)) {
            File::ensureDirectoryExists(app_path('Console/Commands'));
            $commandContent = File::get($sourceRoutes);
            File::put($destinationRoutes, $commandContent);
        }
    }
}
