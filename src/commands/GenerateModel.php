<?php

namespace saloum45\controllergenerate\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateModel extends Command
{
    /**
     * github : saloum45 -> (Salem Dev)
     */
    protected $signature = 'generate:model {model} {--fillable=}';
    protected $description = 'Create a new Eloquent model with a defined $fillable array';

    public function handle(): int
    {
        $name = trim($this->argument('model'));
        $className = Str::studly(class_basename($name));
        $path = app_path("Models/{$className}.php");

        if (File::exists($path)) {
            $this->error("Model {$className} already exists!");
            return self::FAILURE;
        }

        // Extraction des champs renseignés
        $fillableOption = $this->option('fillable');
        $fillableArray = [];

        if ($fillableOption) {
            $fillableArray = array_map(
                fn($item) => trim($item),
                explode(',', $fillableOption)
            );
        }

        // Formatage de la propriété $fillable en PHP
        $fillableFormatted = count($fillableArray) > 0
            ? "[\n        '" . implode("',\n        '", $fillableArray) . "',\n    ]"
            : "[]";

        $stub = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$className} extends Model
{
    use HasFactory;

    protected \$fillable = {$fillableFormatted};
}
PHP;

        File::ensureDirectoryExists(app_path('Models'));
        File::put($path, $stub);

        $this->info("Model {$className} created successfully at app/Models/{$className}.php");

        return self::SUCCESS;
    }
}
