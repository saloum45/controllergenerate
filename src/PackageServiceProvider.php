<?php

namespace dynamic-laravel;

use Illuminate\Support\ServiceProvider;
use saloum45\dynamic-laravel\src\Commands\GenerateControllers;

class PackageServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Enregistrer la commande
        $this->app->singleton(GenerateControllers::class, function () {
            return new GenerateControllers();
        });

        $this->commands([
            GenerateControllers::class,
        ]);
    }

    public function boot()
    {
        //
    }
}
