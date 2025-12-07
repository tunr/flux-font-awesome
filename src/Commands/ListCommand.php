<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Tunr\FluxFontAwesome\Services\IconWriter;

class ListCommand extends Command
{
    protected $signature = 'flux-fa:list
                            {--json : Output as JSON}';

    protected $description = 'List Flux Font Awesome icons';

    public function __construct(
        protected IconWriter $writer,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $outputPath = config('flux-font-awesome.output_path');
        $icons = $this->writer->getIconFiles($outputPath);

        if ($icons === []) {
            if ($this->option('json')) {
                $this->line((string) json_encode(['icons' => [], 'count' => 0], JSON_PRETTY_PRINT));
            } else {
                $this->components->warn('No icons found');
                $this->newLine();
                $this->line('Run <fg=cyan>php artisan flux-fa:build user github</> to build icons');
            }

            return self::SUCCESS;
        }

        sort($icons);

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'icons' => $icons,
                'count' => count($icons),
            ], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->line(implode(' ', $icons));
        $this->newLine();
        $this->components->info(count($icons).' icons');

        return self::SUCCESS;
    }
}
