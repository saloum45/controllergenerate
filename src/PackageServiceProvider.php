<?php

namespace saloum45\controllergenerate;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Saloum45\ControllerGenerate\Http\Controllers\AttributeController;

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
            Commands\ScanProjectCommand::class
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

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'controller-generate-views');

        // Enregistrement de la route POST d'ajout d'attribut
        Route::middleware(['web'])
            ->post('/generate/attributes', [AttributeController::class, 'store'])
            ->name('generate.attributes.store');
    }
}
