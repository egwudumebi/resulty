<?php

namespace App\Console\Commands;

use App\Services\Sample\SemesterTemplateGenerator;
use Illuminate\Console\Command;

class GenerateSampleSemesterExcel extends Command
{
    protected $signature = 'resulty:sample-semester {--output= : Output path}';

    protected $description = 'Generate a sample semester composite Excel file for testing';

    public function handle(SemesterTemplateGenerator $generator): int
    {
        $output = $this->option('output')
            ?: storage_path('app/samples/semester-composite-sample.xlsx');

        $path = $generator->generate($output);

        $this->info("Sample file created: {$path}");

        return self::SUCCESS;
    }
}
