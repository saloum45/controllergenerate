<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScanProjectCommand extends Command
{
    protected $signature = 'generate:scan';

    protected $description = 'Scan the Laravel project';

    public function handle(
        ProjectScanner $scanner
    ): int {
        $data = $scanner->scan();

        $this->line(
            json_encode(
                $data,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            )
        );

        return self::SUCCESS;
    }
}
