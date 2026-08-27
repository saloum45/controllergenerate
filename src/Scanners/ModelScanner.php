<?php

namespace saloum45\controllergenerate\Scanners;

use Illuminate\Support\Facades\File;

class ModelScanner
{
    /**
     * Scan des modèles Laravel.
     */
    public function scan(): array
    {
        $path = app_path('Models');

        if (!File::exists($path)) {
            return [];
        }

        $models = [];

        foreach (File::allFiles($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            $model = $this->parseModel(
                $file,
                $content
            );

            if ($model !== null) {
                $models[] = $model;
            }
        }

        return $models;
    }

    /**
     * Analyse un modèle.
     */
    protected function parseModel(
        $file,
        string $content
    ): ?array {
        $className = $this->extractClassName($content);

        if ($className === null) {
            return null;
        }

        $namespace = $this->extractNamespace($content);

        $class = $namespace
            ? $namespace . '\\' . $className
            : $className;

        /*
         * Vérifie qu'il s'agit bien d'un modèle Eloquent.
         */
        if (!$this->isEloquentModel($content)) {
            return null;
        }

        $fillable = $this->extractFillable($content);

        $guarded = $this->extractGuarded($content);

        $casts = $this->extractCasts($content);

        $hidden = $this->extractHidden($content);

        return [
            'name' => $className,

            'class' => $class,

            'file' => $file->getFilename(),

            'path' => $file->getPathname(),

            'table' => $this->extractTable(
                $content,
                $class
            ),

            'primary_key' => $this->extractPrimaryKey(
                $content
            ),

            'fillable' => $fillable,

            'guarded' => $guarded,

            'casts' => $casts,

            'hidden' => $hidden,

            'relations' => $this->extractRelations(
                $content
            ),
        ];
    }

    /**
     * Vérifie si la classe utilise Eloquent Model ou Authenticatable.
     */
    protected function isEloquentModel(
        string $content
    ): bool {
        return (bool) preg_match(
            '/class\s+\w+\s+extends\s+(?:Model|[A-Za-z0-9_]*Authenticatable[A-Za-z0-9_]*)/',
            $content
        );
    }

    /**
     * Récupère le namespace.
     */
    protected function extractNamespace(
        string $content
    ): ?string {
        if (preg_match(
            '/namespace\s+([^;]+);/',
            $content,
            $matches
        )) {
            return trim($matches[1]);
        }

        return null;
    }

    /**
     * Récupère le nom de la classe.
     */
    protected function extractClassName(
        string $content
    ): ?string {
        if (preg_match(
            '/class\s+([A-Za-z_][A-Za-z0-9_]*)/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Récupère $table.
     */
    protected function extractTable(
        string $content,
        string $class
    ): string {
        if (preg_match(
            '/(?:protected|public)\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        $shortName = class_basename($class);

        return \Illuminate\Support\Str::plural(
            \Illuminate\Support\Str::snake($shortName)
        );
    }

    /**
     * Récupère $primaryKey.
     */
    protected function extractPrimaryKey(
        string $content
    ): string {
        if (preg_match(
            '/(?:protected|public)\s+\$primaryKey\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return 'id';
    }

    /**
     * Récupère $fillable (propriété ou Attribut PHP 8 #[Fillable(...)]).
     */
    protected function extractFillable(
        string $content
    ): array {
        $property = $this->extractArrayProperty($content, 'fillable');
        $attribute = $this->extractPhpAttribute($content, 'Fillable');

        return array_values(array_unique(array_merge($property, $attribute)));
    }

    /**
     * Récupère $guarded (propriété ou Attribut PHP 8 #[Guarded(...)]).
     */
    protected function extractGuarded(
        string $content
    ): array {
        $property = $this->extractArrayProperty($content, 'guarded');
        $attribute = $this->extractPhpAttribute($content, 'Guarded');

        return array_values(array_unique(array_merge($property, $attribute)));
    }

    /**
     * Récupère $hidden (propriété ou Attribut PHP 8 #[Hidden(...)]).
     */
    protected function extractHidden(
        string $content
    ): array {
        $property = $this->extractArrayProperty($content, 'hidden');
        $attribute = $this->extractPhpAttribute($content, 'Hidden');

        return array_values(array_unique(array_merge($property, $attribute)));
    }

    /**
     * Récupère une propriété tableau classique ($fillable = [...]).
     */
    protected function extractArrayProperty(
        string $content,
        string $property
    ): array {
        $pattern =
            '/(?:protected|private|public)\s+\$'
            . preg_quote($property, '/')
            . '\s*=\s*(?:\[|\s*array\s*\()(.*?)(?:\]|\);)/s';

        if (!preg_match($pattern, $content, $matches)) {
            return [];
        }

        return $this->extractStringsFromBlock($matches[1]);
    }

    /**
     * Récupère un attribut PHP 8 (ex: #[Fillable(['nom', 'email'])]).
     */
    protected function extractPhpAttribute(
        string $content,
        string $attributeName
    ): array {
        $pattern = '/#\[' . preg_quote($attributeName, '/') . '\s*\(\s*\[(.*?)\]\s*\)\s*\]/s';

        if (!preg_match($pattern, $content, $matches)) {
            return [];
        }

        return $this->extractStringsFromBlock($matches[1]);
    }

    /**
     * Extrait les chaînes entre guillemets d'un bloc de code.
     */
    protected function extractStringsFromBlock(
        string $block
    ): array {
        preg_match_all('/[\'"]([^\'"]+)[\'"]/', $block, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * Récupère les casts (propriété $casts ou méthode casts()).
     */
    protected function extractCasts(
        string $content
    ): array {
        $casts = [];

        $propertyPattern = '/(?:protected|private|public)\s+\$casts\s*=\s*(?:\[|\s*array\s*\()(.*?)(?:\]|\);)/s';
        $methodPattern = '/function\s+casts\s*\([^)]*\)[^{]*\{.*?return\s+(?:\[|\s*array\s*\()(.*?)(?:\]|\);)/s';

        $body = null;

        if (preg_match($propertyPattern, $content, $matches)) {
            $body = $matches[1];
        } elseif (preg_match($methodPattern, $content, $matches)) {
            $body = $matches[1];
        }

        if ($body !== null) {
            preg_match_all(
                '/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]?([^\'",\]\s]+)[\'"]?/',
                $body,
                $castMatches,
                PREG_SET_ORDER
            );

            foreach ($castMatches as $match) {
                $casts[$match[1]] = trim($match[2]);
            }
        }

        return $casts;
    }

    /**
     * Détecte les relations déclarées dans le modèle.
     */
    protected function extractRelations(
        string $content
    ): array {
        $relations = [];

        $relationTypes = [
            'belongsTo',
            'hasOne',
            'hasMany',
            'belongsToMany',
            'hasManyThrough',
            'hasOneThrough',
            'morphTo',
            'morphOne',
            'morphMany',
            'morphToMany',
            'morphedByMany',
        ];

        $typesPattern = implode('|', $relationTypes);

        preg_match_all(
            '/function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\([^)]*\)[^{]*\{(.*?)\}/s',
            $content,
            $methods,
            PREG_SET_ORDER
        );

        foreach ($methods as $method) {
            $methodName = $method[1];
            $body = $method[2];

            if ($methodName === 'casts') {
                continue;
            }

            if (!preg_match(
                '/->(' . $typesPattern . ')\s*\(/',
                $body,
                $relationMatch
            )) {
                continue;
            }

            $type = $relationMatch[1];

            $relatedModel = $this->extractRelatedModel($body);

            $relations[] = [
                'name' => $methodName,

                'type' => $type,

                'model' => $relatedModel,
            ];
        }

        return $relations;
    }

    /**
     * Récupère le modèle associé à une relation.
     */
    protected function extractRelatedModel(
        string $body
    ): ?string {
        if (preg_match(
            '/(?:belongsTo|hasOne|hasMany|belongsToMany|hasManyThrough|hasOneThrough|morphOne|morphMany|morphToMany|morphedByMany)\s*\(\s*([A-Za-z_][A-Za-z0-9_]*)::class/',
            $body,
            $matches
        )) {
            return $matches[1];
        }

        if (preg_match(
            '/(?:belongsTo|hasOne|hasMany|belongsToMany|hasManyThrough|hasOneThrough|morphOne|morphMany|morphToMany|morphedByMany)\s*\(\s*[\'"]([^\'"]+)[\'"]/',
            $body,
            $matches
        )) {
            return class_basename($matches[1]);
        }

        return null;
    }
}
