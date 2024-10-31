<?php

namespace saloum45\controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrer les commandes
        $this->app->singleton(\App\Console\Commands\GenerateControllers::class, function () {
            return new \App\Console\Commands\GenerateControllers();
        });

        $this->app->singleton(\App\Console\Commands\GenerateRoutesFromControllers::class, function () {
            return new \App\Console\Commands\GenerateRoutesFromControllers();
        });

        $this->app->singleton(\App\Console\Commands\GenerateMigrationFromModels::class, function () {
            return new \App\Console\Commands\GenerateMigrationFromModels();
        });

        $this->commands([
            \App\Console\Commands\GenerateControllers::class,
            \App\Console\Commands\GenerateRoutesFromControllers::class,
            \App\Console\Commands\GenerateMigrationFromModels::class, // Enregistrement de la commande
        ]);
    }

    public function boot()
    {
        // Chemins source et destination pour les commandes
        $commands = [
            'GenerateControllers' => 'GenerateControllers.php',
            'GenerateRoutesFromControllers' => 'GenerateRoutesFromControllers.php',
            'GenerateMigrationFromModels' => 'GenerateMigrationFromModels.php' // Chemin pour GenerateMigrationFromModels
        ];

        foreach ($commands as $command => $file) {
            $source = __DIR__."/Commands/{$file}";
            $destination = app_path("Console/Commands/{$file}");

            // Vérifier et copier si la commande n'existe pas encore
            if (!File::exists($destination)) {
                File::ensureDirectoryExists(app_path('Console/Commands'));
                File::put($destination, File::get($source));
            }
        }
    }
}
