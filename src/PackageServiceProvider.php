<?php

namespace saloum45\controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrer les commandes du package
        $this->commands([
            Commands\GenerateControllers::class,
            Commands\GenerateRoutes::class,
            Commands\GenerateMigrations::class,
            Commands\GenerateSeeders::class,
            Commands\GenerateAngularJson::class,
            Commands\GenerateRelations::class,
            Commands\GenerateAll::class,
        ]);
    }

    public function boot()
    {
        // Garder seulement la copie du trait
        $sourceTrait = __DIR__ . '/Commands/GenerateApiResponse.php';
        $destinationTrait = app_path('Traits/GenerateApiResponse.php');

        if (!File::exists($destinationTrait)) {
            File::ensureDirectoryExists(app_path('Traits'));
            File::copy($sourceTrait, $destinationTrait);
        }
    }
}
