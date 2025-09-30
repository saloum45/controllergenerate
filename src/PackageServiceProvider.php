<?php

namespace saloum45\controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class PackageServiceProvider extends ServiceProvider
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻
    public function register()
    {
        // Enregistrer les commandes
        $this->app->singleton(\App\Console\Commands\GenerateControllers::class, function () {
            return new \App\Console\Commands\GenerateControllers();
        });

        $this->app->singleton(\App\Console\Commands\GenerateRoutes::class, function () {
            return new \App\Console\Commands\GenerateRoutes();
        });

        $this->app->singleton(\App\Console\Commands\GenerateMigrations::class, function () {
            return new \App\Console\Commands\GenerateMigrations();
        });

        $this->app->singleton(\App\Console\Commands\GenerateSeeders::class, function () {
            return new \App\Console\Commands\GenerateSeeders();
        });
        $this->app->singleton(\App\Console\Commands\GenerateAngularJson::class, function () {
            return new \App\Console\Commands\GenerateAngularJson();
        });
        $this->app->singleton(\App\Console\Commands\GenerateRelations::class, function () {
            return new \App\Console\Commands\GenerateRelations();
        });
        $this->app->singleton(\App\Console\Commands\GenerateAll::class, function () {
            return new \App\Console\Commands\GenerateAll();
        });

        $this->commands([
            \App\Console\Commands\GenerateControllers::class,
            \App\Console\Commands\GenerateRoutes::class,
            \App\Console\Commands\GenerateMigrations::class, // Enregistrement de la commande
            \App\Console\Commands\GenerateSeeders::class,
            \App\Console\Commands\GenerateAngularJson::class,
            \App\Console\Commands\GenerateRelations::class,
            \App\Console\Commands\GenerateAll::class
        ]);
    }

    public function boot()
    {
        // Chemins source et destination pour les commandes
        $commands = [
            'GenerateControllers' => 'GenerateControllers.php',
            'GenerateRoutes' => 'GenerateRoutes.php',
            'GenerateMigrations' => 'GenerateMigrations.php', // Chemin pour GenerateMigrations
            'GenerateSeeders' => 'GenerateSeeders.php',
            'GenerateAngularJson' => 'GenerateAngularJson.php',
            'GenerateRelations' => 'GenerateRelations.php',
            'GenerateAll' => 'GenerateAll.php'
        ];

        foreach ($commands as $command => $file) {
            $source = __DIR__ . "/Commands/{$file}";
            $destination = app_path("Console/Commands/{$file}");

            // Vérifier et copier si la commande n'existe pas encore
            if (!File::exists($destination)) {
                File::ensureDirectoryExists(app_path('Console/Commands'));
                File::put($destination, File::get($source));
            }
        }

        // Chemins source et destination pour les traits
        $sourceTrait = __DIR__ . '/Commands/GenerateApiResponse.php';
        $destinationTrait = app_path('Traits/GenerateApiResponse.php');

        if (!File::exists($destinationTrait)) {
            File::ensureDirectoryExists(app_path('Traits'));
            File::copy($sourceTrait, $destinationTrait);
        }
    }
}
