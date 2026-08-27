<?php

namespace Saloum45\ControllerGenerate\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Saloum45\ControllerGenerate\Services\AttributeManager;
use Illuminate\Support\Facades\Artisan;

class AttributeController
{
    public function store(
        Request $request,
        AttributeManager $attributeManager
    ): JsonResponse {
        $validated = $request->validate([
            'attribute'       => 'required|string',
            'type'            => 'required|string',
            'table'           => 'nullable|string',
            'model_path'      => 'nullable|string',
            'migration_path'  => 'nullable|string',
            'controller_path' => 'nullable|string',
        ]);

        try {
            $attributeManager->addAttributePaths(
                attribute: $validated['attribute'],
                type: $validated['type'],
                table: $validated['table'] ?? null,
                modelPath: $validated['model_path'] ?? null,
                migrationPath: $validated['migration_path'] ?? null,
                controllerPath: $validated['controller_path'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => "Attribut '{$validated['attribute']}' ajouté avec succès !"
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Liste des commandes autorisées à l'exécution.
     */
    protected array $allowedCommands = [
        'generate:all',
        'generate:controllers',
        'generate:relations',
        'generate:migrations',
        'generate:routes',
    ];

    /**
     * Exécute une commande Artisan depuis la vue.
     */
    public function execute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string'],
            'model' => ['nullable', 'string'],
        ]);

        $command = $validated['command'];
        $model = $validated['model'] ?? null;

        // Sécurité : Vérifie que la commande demandée est bien autorisée
        if (!in_array($command, $this->allowedCommands, true)) {
            return response()->json([
                'success' => false,
                'message' => "Commande non autorisée : {$command}",
            ], 403);
        }

        // Préparation des paramètres
        $parameters = [];
        if (!empty($model)) {
            $parameters['model'] = $model;
        }

        try {
            // Exécution de la commande Artisan
            $exitCode = Artisan::call($command, $parameters);
            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'command' => $command . ($model ? " {$model}" : ''),
                'output' => $output,
                'message' => $exitCode === 0
                    ? 'Exécution terminée avec succès.'
                    : 'L\'exécution a rencontré des erreurs.',
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
