<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GuidelinesCommand extends Command
{
    protected $signature = 'flux-fa:guidelines
                            {--force : Overwrite existing guidelines}';

    protected $description = 'Publish AI guidelines for Laravel Boost';

    public function handle(): int
    {
        $guidelinesPath = base_path('.ai/guidelines/flux-font-awesome.md');
        $force = $this->option('force');

        if (File::exists($guidelinesPath) && ! $force) {
            $this->components->warn('Guidelines already exist at .ai/guidelines/flux-font-awesome.md');
            $this->line('  <fg=gray>Use --force to overwrite</>');

            return self::SUCCESS;
        }

        $this->call('vendor:publish', [
            '--tag' => 'flux-font-awesome-guidelines',
            '--force' => $force,
        ]);

        $this->components->info('Published AI guidelines to .ai/guidelines/flux-font-awesome.md');

        return self::SUCCESS;
    }
}
