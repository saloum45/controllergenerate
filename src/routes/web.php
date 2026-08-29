<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Saloum45\ControllerGenerate\Http\Controllers\AttributeController;

Route::middleware(['web'])->group(function () {
    // Accès immédiat à la documentation
    Route::get('/generate/docs', function () {
        Artisan::call('generate:scan');
        $jsonOutput = Artisan::output();
        $project = json_decode($jsonOutput, true);

        return view('controller-generate-views::documentation', compact('project'));
    })->name('generator.docs');

    // Exécution des commandes depuis l'interface
    Route::post('/generator/execute', [AttributeController::class, 'execute'])
        ->name('generator.execute');
});
