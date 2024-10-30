<?php

namespace controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrer la commande
        $this->app->singleton(\App\Console\Commands\GenerateControllers::class, function () {
            return new \App\Console\Commands\GenerateControllers();
        });

        $this->commands([
            \App\Console\Commands\GenerateControllers::class,
        ]);
    }

    public function boot()
    {
        // Chemin source de la commande dans le package
        $source = __DIR__.'/Commands/GenerateControllers.php';

        // Chemin de destination pour la commande dans le dossier app/Console/Commands
        $destination = app_path('Console/Commands/GenerateControllers.php');

        // Si le fichier de commande n'existe pas, le créer et y copier le contenu du fichier source
        if (!File::exists($destination)) {
            File::ensureDirectoryExists(app_path('Console/Commands'));

            // Lire le contenu de la commande source et l'écrire dans la destination
            $commandContent = File::get($source);
            File::put($destination, $commandContent);
        }
    }
}
