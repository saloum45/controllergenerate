<?php


namespace saloum45\controllergenerate\Scanners;

use Illuminate\Support\Facades\File;

class ControllerScanner
{
    /**
     * Scan tous les controllers du projet.
     */
    public function scan(): array
    {
        $path = app_path('Http/Controllers');

        if (!File::exists($path)) {
            return [];
        }

        $controllers = [];

        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            $controller = $this->parseController(
                $file,
                $content
            );

            if ($controller !== null) {
                $controllers[] = $controller;
            }
        }

        return $controllers;
    }

    /**
     * Analyse un controller.
     */
    protected function parseController(
        $file,
        string $content
    ): ?array {
        $className = $this->extractClassName($content);

        if ($className === null) {
            return null;
        }

        return [
            'name' => $className,
            'class' => $this->extractFullClassName($content, $className),
            'file' => $file->getFilename(),
            'path' => $file->getPathname(),
            'methods' => $this->extractMethods($content),
        ];
    }

    /**
     * Récupère le nom de la classe.
     */
    protected function extractClassName(
        string $content
    ): ?string {
        if (!preg_match(
            '/class\s+([A-Za-z_][A-Za-z0-9_]*)/',
            $content,
            $matches
        )) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Récupère le namespace + classe.
     */
    protected function extractFullClassName(
        string $content,
        string $className
    ): string {
        if (preg_match(
            '/namespace\s+([^;]+);/',
            $content,
            $matches
        )) {
            return trim($matches[1]) . '\\' . $className;
        }

        return $className;
    }

    /**
     * Récupère les méthodes publiques/protégées/privées.
     */
    protected function extractMethods(
        string $content
    ): array {
        preg_match_all(
            '/(?:public|protected|private)?\s*function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/',
            $content,
            $matches
        );

        return array_values(
            array_unique($matches[1])
        );
    }
}
