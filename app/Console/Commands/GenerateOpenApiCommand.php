<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class GenerateOpenApiCommand extends Command
{
    protected $signature = 'openapi:generate';
    protected $description = 'Generate OpenAPI (Swagger) specification into public/openapi.json';

    public function handle(): int
    {
        $this->info('Generating OpenAPI documentation...');

        $process = new Process([
            'vendor/bin/openapi',
            'app',
            '--exclude', 'tests',
            '--exclude', 'database',
            '--exclude', 'storage',
            '-o', 'public/openapi.json',
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            $this->error("Error:");
            $this->error($process->getErrorOutput());

            return Command::FAILURE;
        }

        $this->info('OpenAPI documentation generated: public/openapi.json');

        return Command::SUCCESS;
    }
}
