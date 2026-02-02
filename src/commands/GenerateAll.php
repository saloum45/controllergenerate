<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateAll extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇
    protected $signature = 'generate:all {model?}';
    protected $description = 'Generate Relations, Controllers, Routes, and Migrations for all or a specific model';

    public function handle()
    {
        $modelName = $this->argument('model');
        $this->info("Début de la génération " . ($modelName ? "pour le modèle $modelName" : "pour tous les modèles"));

        // 1️⃣ Générer les relations
        $this->call('generate:relations', ['model' => $modelName]);

        // 2️⃣ Générer les controllers
        $this->call('generate:controllers', ['model' => $modelName]);

        // 3️⃣ Générer les routes
        $this->call('generate:routes', ['model' => $modelName]);

        // 4️⃣ Générer les migrations
        $this->call('generate:migrations', ['model' => $modelName]);

        $this->info("✅ Génération complète terminée.");
        $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 😇");
    }
}
