<?php

namespace Saloum45\ControllerGenerate\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Saloum45\ControllerGenerate\Services\AttributeManager;

class AttributeController
{
    public function store(
        Request $request,
        AttributeManager $attributeManager
    ): JsonResponse {
        $validated = $request->validate([
            'attribute' => 'required|string',
            'type' => 'required|string',
            'model_path' => 'nullable|string',
            'migration_path' => 'nullable|string',
            'controller_path' => 'nullable|string',
        ]);

        try {
            $attributeManager->addAttributePaths(
                attribute: $validated['attribute'],
                type: $validated['type'],
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
}
