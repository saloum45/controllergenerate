<?php

namespace Saloum45\ControllerGenerate\Modifiers;

use RuntimeException;

class ModelModifier
{
    /**
     * Ajoute un attribut dans $fillable du modèle.
     */
    public function addAttribute(
        string $path,
        string $attribute
    ): void {
        if (! file_exists($path)) {
            throw new RuntimeException(
                "Model file not found: {$path}"
            );
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(
                "Unable to read model file: {$path}"
            );
        }

        /*
         * Vérifie si l'attribut existe déjà
         * dans le $fillable.
         */
        if ($this->hasFillableAttribute($content, $attribute)) {
            return;
        }

        /*
         * Recherche précisément :
         *
         * protected $fillable = [
         *     ...
         * ];
         */
        $pattern = '/(protected\s+\$fillable\s*=\s*\[)(.*?)(\];)/s';

        if (! preg_match($pattern, $content, $matches)) {
            throw new RuntimeException(
                "The \$fillable property was not found in: {$path}"
            );
        }

        /*
         * On conserve exactement
         * le contenu existant.
         */
        $fillable = $matches[2];

        /*
         * Détermine l'indentation utilisée.
         */
        $indentation = $this->detectIndentation($fillable);

        $fillable .=
            $indentation .
            ",'" .
            $attribute .
            "',\n";

        $replacement =
            $matches[1] .
            $fillable .
            $matches[3];

        $content = str_replace(
            $matches[0],
            $replacement,
            $content
        );

        if (
            file_put_contents($path, $content) === false
        ) {
            throw new RuntimeException(
                "Unable to write model file: {$path}"
            );
        }
    }

    /**
     * Vérifie si l'attribut existe déjà dans $fillable.
     */
    private function hasFillableAttribute(
        string $content,
        string $attribute
    ): bool {
        return preg_match(
            '/protected\s+\$fillable\s*=\s*\[(.*?)]\s*;/s',
            $content,
            $matches
        )
            && preg_match(
                "/['\"]" .
                preg_quote($attribute, '/') .
                "['\"]/",
                $matches[1]
            );
    }

    /**
     * Détecte l'indentation utilisée
     * dans le tableau $fillable.
     */
    private function detectIndentation(
        string $fillable
    ): string {
        if (
            preg_match(
                '/\n([ \t]+)[\'"]/',
                $fillable,
                $matches
            )
        ) {
            return $matches[1];
        }

        return '        ';
    }
}
