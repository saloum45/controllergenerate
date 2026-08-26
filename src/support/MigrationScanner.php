<?php


namespace App\Console\Commands;

use Illuminate\Support\Facades\File;

class MigrationScanner
{
    /**
     * Tables Laravel / framework à ignorer.
     */
    protected array $ignoredTables = [
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'sessions',
        'personal_access_tokens'
    ];

    /**
     * Scan des migrations du projet.
     */
    public function scan(): array
    {
        $path = database_path('migrations');

        if (!File::exists($path)) {
            return [];
        }

        $migrations = [];

        foreach (File::files($path) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());

            $migration = $this->parseMigration(
                $file->getFilename(),
                $content
            );

            if ($migration !== null) {
                $migrations[] = $migration;
            }
        }

        return $migrations;
    }

    /**
     * Analyse une migration.
     */
    protected function parseMigration(
        string $filename,
        string $content
    ): ?array {
        $table = $this->extractTableName($content);

        if ($table === null) {
            return null;
        }

        /*
         * Ignorer les tables gérées par Laravel.
         */
        if ($this->shouldIgnore($table)) {
            return null;
        }

        return [
            'file' => $filename,
            'table' => $table,
            'columns' => $this->extractColumns($content),
        ];
    }

    /**
     * Récupère le nom de la table depuis Schema::create().
     */
    protected function extractTableName(string $content): ?string
    {
        if (!preg_match(
            "/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*function\s*\([^)]*\)\s*\{/m",
            $content,
            $matches
        )) {
            return null;
        }

        return $matches[1];
    }

    /**
     * Vérifie si la table doit être ignorée.
     */
    protected function shouldIgnore(string $table): bool
    {
        return in_array(
            $table,
            $this->ignoredTables,
            true
        );
    }

    /**
     * Extrait uniquement les attributs définis dans
     * le Schema::create().
     */
    protected function extractColumns(string $content): array
    {
        $columns = [];

        /*
         * Récupérer uniquement le contenu du
         * Schema::create(...).
         */
        $schemaBlock = $this->extractSchemaBlock($content);

        if ($schemaBlock === null) {
            return [];
        }

        /*
         * Rechercher les définitions :
         *
         * $table->string('nom');
         * $table->integer('age');
         * $table->boolean('active')->default(true);
         */
        preg_match_all(
            '/\$table->(\w+)\s*\(\s*[\'"]([^\'"]+)[\'"]([^)]*)\)/',
            $schemaBlock,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            $type = $match[1];
            $name = $match[2];
            $options = $match[3];

            /*
             * Ignorer tout ce qui est géré automatiquement
             * par Laravel.
             */
            if ($this->isLaravelManagedColumn($type, $name)) {
                continue;
            }

            $columns[] = [
                'name' => $name,
                'type' => $type,
                'nullable' => str_contains(
                    $options,
                    '->nullable()'
                ),
                'unique' => str_contains(
                    $options,
                    '->unique()'
                ),
                'default' => $this->extractDefault($options),
            ];
        }

        return $this->removeDuplicateColumns($columns);
    }

    /**
     * Extrait le contenu du callback de Schema::create().
     *
     * Exemple :
     *
     * Schema::create('users', function (Blueprint $table) {
     *
     *     $table->string('nom');
     *
     * });
     */
    protected function extractSchemaBlock(
        string $content
    ): ?string {
        if (!preg_match(
            "/Schema::create\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*function\s*\([^)]*\)\s*\{/m",
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            return null;
        }

        /*
         * Position juste après {
         */
        $start = $matches[0][1] + strlen($matches[0][0]);

        $depth = 1;
        $length = strlen($content);

        for ($i = $start; $i < $length; $i++) {

            if ($content[$i] === '{') {
                $depth++;
            }

            if ($content[$i] === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr(
                        $content,
                        $start,
                        $i - $start
                    );
                }
            }
        }

        return null;
    }

    /**
     * Détermine si une colonne est gérée
     * automatiquement par Laravel.
     */
    protected function isLaravelManagedColumn(
        string $type,
        string $name
    ): bool {
        /*
         * Méthodes Laravel qui génèrent automatiquement
         * des colonnes.
         */
        if (in_array($type, [
            'id',
            'timestamps',
            'nullableTimestamps',
            'softDeletes',
            'softDeletesTz',
            'rememberToken',
        ], true)) {
            return true;
        }

        /*
         * Colonnes conventionnelles Laravel.
         */
        if (in_array($name, [
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
        ], true)) {
            return true;
        }

        return false;
    }

    /**
     * Extrait default().
     */
    protected function extractDefault(
        string $options
    ): mixed {
        if (!preg_match(
            "/->default\s*\(\s*([^)]+)\s*\)/",
            $options,
            $matches
        )) {
            return null;
        }

        $value = trim($matches[1]);

        /*
         * String.
         */
        if (
            (
                str_starts_with($value, "'")
                && str_ends_with($value, "'")
            )
            ||
            (
                str_starts_with($value, '"')
                && str_ends_with($value, '"')
            )
        ) {
            return trim($value, "'\"");
        }

        /*
         * Boolean.
         */
        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        /*
         * Null.
         */
        if ($value === 'null') {
            return null;
        }

        /*
         * Numérique.
         */
        if (is_numeric($value)) {
            return str_contains($value, '.')
                ? (float) $value
                : (int) $value;
        }

        return $value;
    }

    /**
     * Supprime les doublons.
     */
    protected function removeDuplicateColumns(
        array $columns
    ): array {
        $result = [];
        $existing = [];

        foreach ($columns as $column) {
            $name = $column['name'];

            if (isset($existing[$name])) {
                continue;
            }

            $existing[$name] = true;

            $result[] = $column;
        }

        return $result;
    }
}
