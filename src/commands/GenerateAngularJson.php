<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class GenerateAngularJson extends Command
{
    // github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻
    protected $signature = 'generate:angular {table?}';
    protected $description = 'Génère un fichier JSON de configuration à partir de la base de données';

    public function handle()
    {
        $databaseName = DB::getDatabaseName();
        $keyName = "Tables_in_$databaseName";
        $tableParam = $this->argument('table');

        if ($tableParam) {
            if (!Schema::hasTable($tableParam)) {
                $this->error("La table '{$tableParam}' n'existe pas.");
                return Command::FAILURE;
            }

            $tables = [
                (object) [$keyName => $tableParam]
            ];
        } else {
            $tables = DB::select("SHOW TABLES");
        }

        $json = [
            'projectName' => 'projet1.angular',
            'decription' => 'Fichier de configuration de Taf',
            'taf_base_url' => 'http://localhost:8000/api/',
            'les_modules' => [
                [
                    'module' => 'home',
                    'les_tables' => []
                ]
            ]
        ];
        if (!$tableParam) {
            $json['les_modules'][] = [
                'module' => 'public',
                'les_tables' => [
                    [
                        'table' => 'login',
                        'description' => ['login', 'mot_de_passe'],
                        'les_types' => ['login']
                    ]
                ]
            ];
        }


        $ignoredTables = [
            'migrations',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
            'cache',
            'cache_locks',
            'job_batches',
            'jobs',
            'password_reset_tokens',
            'sessions',
        ];

        foreach ($tables as $table) {
            if (in_array($table->$keyName, $ignoredTables)) {
                continue;
            }
            $tableName = $table->$keyName;
            $columns = DB::select("SHOW FULL COLUMNS FROM $tableName");
            $foreignKeys = DB::select("
                SELECT
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = ?
                AND TABLE_SCHEMA = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName, $databaseName]);

            $columnDetails = [];
            $basedTables = [$tableName];
            $referencedTables = [];

            foreach ($columns as $column) {
                $col = [
                    'Field' => $column->Field,
                    'Type' => $column->Type,
                    'Null' => $column->Null,
                    'Key' => $column->Key,
                    'Default' => $column->Default,
                    'Extra' => $column->Extra,
                    'explications' => ''
                ];

                // Vérifie si c'est une clé étrangère
                $fk = collect($foreignKeys)->firstWhere('COLUMN_NAME', $column->Field);
                if ($fk) {
                    $referencedTables[] = $fk->REFERENCED_TABLE_NAME;
                    $basedTables[] = $fk->REFERENCED_TABLE_NAME;
                    $col['explications'] = "clé étrangère liée à la colonne {$fk->REFERENCED_COLUMN_NAME} de la table {$fk->REFERENCED_TABLE_NAME}";
                    $col['table'] = [
                        'TABLE_NAME' => $tableName,
                        'COLUMN_NAME' => $column->Field,
                        'REFERENCED_TABLE_NAME' => $fk->REFERENCED_TABLE_NAME,
                        'REFERENCED_COLUMN_NAME' => $fk->REFERENCED_COLUMN_NAME,
                    ];

                    // Ajoute les détails de la table référencée
                    $refColumns = DB::select("SHOW FULL COLUMNS FROM {$fk->REFERENCED_TABLE_NAME}");
                    $refColDetails = [];
                    foreach ($refColumns as $rc) {
                        $refColDetails[] = [
                            'Field' => $rc->Field,
                            'Type' => $rc->Type,
                            'Null' => $rc->Null,
                            'Key' => $rc->Key,
                            'Default' => $rc->Default,
                            'Extra' => $rc->Extra,
                            'explications' => ''
                        ];
                    }

                    $col['table_existant'] = false;
                    $col['referenced_table'] = [
                        'table_name' => $fk->REFERENCED_TABLE_NAME,
                        'cle_primaire' => $refColDetails[0],
                        'les_based_table_name' => array_values(array_unique([$tableName, $fk->REFERENCED_TABLE_NAME])),
                        'les_referenced_table' => [],
                        'les_colonnes' => $refColDetails
                    ];
                }

                $columnDetails[] = $col;
            }
            $filteredColumnDetails = array_filter($columnDetails, function ($col) {
                return !in_array($col['Field'], ['created_at', 'updated_at']);
            });
            $tableJson = [
                'table' => $tableName,
                'description' => array_values(array_filter(array_column($columnDetails, 'Field'), function ($field) {
                    return !in_array($field, ['created_at', 'updated_at']);
                })),

                'table_descriptions' => [
                    'table_name' => $tableName,
                    'cle_primaire' => collect($columnDetails)->firstWhere('Key', 'PRI'),
                    'les_based_table_name' => array_values(array_unique($basedTables)),
                    'les_referenced_table' => array_values(array_unique($referencedTables)),
                    'les_colonnes' => array_values($filteredColumnDetails)
                ],
                'les_types' => ['add', 'edit', 'list', 'details']
            ];

            $json['les_modules'][0]['les_tables'][] = $tableJson;
        }

        File::put(base_path('taf.config.json'), json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info('Fichier taf_config.json généré avec succès.');
        $this->info("github : saloum45 -> (Salem Dev) fait avec beaucoup ❤️ et ☕️ enjoy it 🧑🏽‍💻");
    }
}
