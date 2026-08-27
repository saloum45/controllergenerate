<?php

namespace Saloum45\ControllerGenerate\Modifiers;

use RuntimeException;

class MigrationModifier
{
    /**
     * Ajoute une colonne dans la migration du modèle.
     */
    public function addAttribute(
        string $path,
        string $attribute,
        string $type,
        bool $nullable = false,
        bool $unique = false
    ): void {
        if (! file_exists($path)) {
            throw new RuntimeException("Migration file not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("Unable to read migration file: {$path}");
        }

        /*
         * Ne pas ajouter deux fois la même colonne.
         */
        if ($this->hasColumn($content, $attribute)) {
            return;
        }

        /*
         * Recherche le bloc Schema::create().
         */
        $pattern = '/(Schema::create\s*\([^;]+?\)\s*\{)(.*?)(\n\s*\}\);)/s';

        if (! preg_match($pattern, $content, $matches)) {
            throw new RuntimeException("Schema::create block not found in: {$path}");
        }

        $header = $matches[1];
        $schemaContent = $matches[2];
        $footer = $matches[3];

        /*
         * Génération de la déclaration Laravel.
         */
        $column = $this->buildColumn(
            $attribute,
            $type,
            $nullable,
            $unique
        );

        /*
         * Détecte l'indentation de la méthode $table->.
         */
        $indentation = $this->detectIndentation($schemaContent);

        /*
         * Injection propre avec le bon saut de ligne et l'indentation alignée.
         */
        $schemaContent = rtrim($schemaContent) . "\n" . $indentation . $column . "\n";

        $replacement = $header . $schemaContent . $footer;

        $content = str_replace(
            $matches[0],
            $replacement,
            $content
        );

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException("Unable to write migration file: {$path}");
        }
    }

    /**
     * Change uniquement le type d'une colonne existante.
     */
    public function changeAttributeType(
        string $path,
        string $attribute,
        string $type
    ): void {
        if (! file_exists($path)) {
            throw new RuntimeException("Migration file not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException("Unable to read migration file: {$path}");
        }

        $pattern = '/(\$table->)[a-zA-Z_][a-zA-Z0-9_]*(\s*\(\s*[\'"]' . preg_quote($attribute, '/') . '[\'"]\s*\)[^;]*;)/';

        if (! preg_match($pattern, $content)) {
            throw new RuntimeException("Column '{$attribute}' not found in migration.");
        }

        $content = preg_replace(
            $pattern,
            '$1' . $type . '$2',
            $content,
            1
        );

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException("Unable to write migration file: {$path}");
        }
    }

    /**
     * Vérifie si une colonne existe déjà dans le bloc de migration.
     */
    private function hasColumn(string $content, string $attribute): bool
    {
        return preg_match(
            '/\$table->[a-zA-Z_][a-zA-Z0-9_]*\s*\(\s*[\'"]' . preg_quote($attribute, '/') . '[\'"]/',
            $content
        ) === 1;
    }

    /**
     * Génère une déclaration Blueprint.
     */
    private function buildColumn(
        string $attribute,
        string $type,
        bool $nullable,
        bool $unique
    ): string {
        $column = "\$table->{$type}('{$attribute}')";

        if ($nullable) {
            $column .= '->nullable()';
        }

        if ($unique) {
            $column .= '->unique()';
        }

        return $column . ';';
    }

    /**
     * Détecte l'indentation du Schema::create().
     */
    private function detectIndentation(string $schemaContent): string
    {
        if (preg_match('/\n([ \t]+)\$table->/', $schemaContent, $matches)) {
            return $matches[1];
        }

        return '            ';
    }
}
