<?php

namespace Saloum45\ControllerGenerate\Modifiers;

use RuntimeException;

class ControllerModifier
{
    /**
     * Ajoute un attribut dans les méthodes store() et update() d'un contrôleur.
     */
    public function addAttribute(string $path, string $attribute): void
    {
        if (! file_exists($path)) {
            throw new RuntimeException("Controller file not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("Unable to read controller file: {$path}");
        }

        $modified = false;

        // 1. Traitement de la méthode store()
        if ($this->hasMethod($content, 'store')) {
            $content = $this->injectAttributeInMethod($content, 'store', $attribute, $modified);
        }

        // 2. Traitement de la méthode update()
        if ($this->hasMethod($content, 'update')) {
            $content = $this->injectAttributeInMethod($content, 'update', $attribute, $modified);
        }

        // Sauvegarde si des modifications ont été effectuées
        if ($modified) {
            if (file_put_contents($path, $content) === false) {
                throw new RuntimeException("Unable to write controller file: {$path}");
            }
        }
    }

    /**
     * Injecte l'assignation de l'attribut dans le corps de la méthode ciblée.
     */
    protected function injectAttributeInMethod(
        string $content,
        string $methodName,
        string $attribute,
        bool &$modified
    ): string {
        // Détecte la méthode et isole son bloc jusqu'au premier enregistrement ou affectation
        $pattern = '/(public\s+function\s+' . $methodName . '\s*\([^)]*\)\s*\{)(.*?)(\}\s*\n|\}\s*$)/s';

        if (! preg_match($pattern, $content, $matches)) {
            return $content;
        }

        $header = $matches[1];
        $body = $matches[2];
        $footer = $matches[3];

        // Vérification si l'attribut est déjà assigné dans cette méthode
        if (preg_match('/->\s*' . preg_quote($attribute, '/') . '\s*=/', $body)) {
            return $content;
        }

        // Recherche du nom de la variable d'instance (ex: $gpsAlarme dans "$gpsAlarme->imei = ...")
        if (! preg_match('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*->\s*[a-zA-Z0-9_]+\s*=/', $body, $varMatches)) {
            return $content;
        }

        $variableName = $varMatches[1];

        // Repère la dernière affectation de la variable (ex: $gpsAlarme->adresse = $request->adresse;)
        $assignmentPattern = '/(\$' . preg_quote($variableName, '/') . '\s*->\s*[a-zA-Z0-9_]+\s*=\s*\$request\s*->\s*[a-zA-Z0-9_]+;)/';

        if (preg_match_all($assignmentPattern, $body, $assignments, PREG_OFFSET_CAPTURE)) {
            $lastAssignment = end($assignments[0]);
            $lastAssignmentText = $lastAssignment[0];
            $offset = $lastAssignment[1];

            // Détecte l'indentation de la ligne
            $indentation = $this->detectLineIndentation($body, $offset);

            $newAssignment = "\n" . $indentation . '$' . $variableName . '->' . $attribute . ' = $request->' . $attribute . ';';

            // Injection juste après la dernière assignation trouvée
            $newBody = substr_replace(
                $body,
                $lastAssignmentText . $newAssignment,
                $offset,
                strlen($lastAssignmentText)
            );

            $modified = true;
            return str_replace($matches[0], $header . $newBody . $footer, $content);
        }

        return $content;
    }

    /**
     * Vérifie si une méthode existe dans le fichier.
     */
    private function hasMethod(string $content, string $methodName): bool
    {
        return preg_match('/public\s+function\s+' . $methodName . '\s*\(/', $content) === 1;
    }

    /**
     * Détecte l'indentation de la ligne courante.
     */
    private function detectLineIndentation(string $content, int $offset): string
    {
        $lineStart = strrpos(substr($content, 0, $offset), "\n");

        if ($lineStart === false) {
            return '            ';
        }

        $line = substr($content, $lineStart + 1, $offset - $lineStart - 1);

        if (preg_match('/^([ \t]+)/', $line, $matches)) {
            return $matches[1];
        }

        return '            ';
    }
}
