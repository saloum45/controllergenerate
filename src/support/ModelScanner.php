<?php


namespace App\Console\Commands;

use Illuminate\Support\Facades\File;
use ReflectionClass;

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

        $fillable = $this->extractArrayProperty(
            $content,
            'fillable'
        );

        $guarded = $this->extractArrayProperty(
            $content,
            'guarded'
        );

        $casts = $this->extractArrayProperty(
            $content,
            'casts'
        );

        $hidden = $this->extractArrayProperty(
            $content,
            'hidden'
        );

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
     * Vérifie si la classe utilise Eloquent Model.
     *
     * Exemple :
     *
     * class User extends Model
     */
    protected function isEloquentModel(
        string $content
    ): bool {
        return (bool) preg_match(
            '/class\s+\w+\s+extends\s+(?:Model|Authenticatable)/',
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
     *
     * Si le modèle ne définit pas $table,
     * Laravel utilisera la convention.
     */
    protected function extractTable(
        string $content,
        string $class
    ): string {
        if (preg_match(
            '/protected\s+\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        /*
         * Convention Laravel :
         *
         * Vehicule -> vehicules
         * User -> users
         */
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
            '/protected\s+\$primaryKey\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/',
            $content,
            $matches
        )) {
            return $matches[1];
        }

        return 'id';
    }

    /**
     * Récupère une propriété tableau Laravel.
     *
     * Exemple :
     *
     * protected $fillable = [
     *     'nom',
     *     'email',
     * ];
     */
    protected function extractArrayProperty(
        string $content,
        string $property
    ): array {
        /*
         * Supporte :
         *
         * protected $fillable = [...]
         * public $fillable = [...]
         * protected $casts = [...]
         */
        $pattern =
            '/(?:protected|private|public)\s+\$'
            . preg_quote($property, '/')
            . '\s*=\s*\[(.*?)\];/s';

        if (!preg_match(
            $pattern,
            $content,
            $matches
        )) {
            return [];
        }

        $body = $matches[1];

        $values = [];

        /*
         * Récupère :
         *
         * 'nom'
         * "nom"
         */
        preg_match_all(
            '/[\'"]([^\'"]+)[\'"]/',
            $body,
            $valueMatches
        );

        foreach ($valueMatches[1] as $value) {
            $values[] = $value;
        }

        return array_values(
            array_unique($values)
        );
    }

    /**
     * Détecte les relations déclarées dans le modèle.
     *
     * Exemple :
     *
     * public function user()
     * {
     *     return $this->belongsTo(User::class);
     * }
     */
    protected function extractRelations(
        string $content
    ): array {
        $relations = [];

        /*
         * Méthodes de relations Eloquent supportées.
         */
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

        $typesPattern = implode(
            '|',
            $relationTypes
        );

        /*
         * Cherche une méthode qui retourne
         * une relation Eloquent.
         */
        preg_match_all(
            '/function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\([^)]*\).*?\{(.*?)\}/s',
            $content,
            $methods,
            PREG_SET_ORDER
        );

        foreach ($methods as $method) {
            $methodName = $method[1];
            $body = $method[2];

            if (!preg_match(
                '/->(' . $typesPattern . ')\s*\(/',
                $body,
                $relationMatch
            )) {
                continue;
            }

            $type = $relationMatch[1];

            $relatedModel = $this->extractRelatedModel(
                $body
            );

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
     *
     * belongsTo(User::class)
     * hasMany(Vehicule::class)
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

        return null;
    }
}
