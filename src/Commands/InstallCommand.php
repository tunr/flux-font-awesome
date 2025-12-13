<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;

class InstallCommand extends Command
{
    protected $signature = 'flux-fa:install
                            {--force : Overwrite existing configuration}';

    protected $description = 'Install Flux Font Awesome package';

    public function __construct(
        protected FontAwesomePathResolver $pathResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->components->info('Installing Flux Font Awesome...');

        // Publish configuration
        $this->publishConfiguration();

        // Create icon directory
        $this->createIconDirectory();

        // Check Font Awesome installation
        $this->checkFontAwesome();

        // Publish AI guidelines if Boost is installed
        $this->publishGuidelines();

        $this->newLine();
        $this->components->info('Flux Font Awesome installed successfully!');
        $this->newLine();

        // Next steps
        $this->displayNextSteps();

        return self::SUCCESS;
    }

    protected function publishConfiguration(): void
    {
        $configPath = config_path('flux-font-awesome.php');
        $force = $this->option('force');

        if (File::exists($configPath) && ! $force) {
            $this->components->warn('Configuration already exists. Use --force to overwrite.');

            return;
        }

        $this->call('vendor:publish', [
            '--tag' => 'flux-font-awesome-config',
            '--force' => $force,
        ]);

        $this->components->task('Published configuration');
    }

    protected function createIconDirectory(): void
    {
        $iconPath = config('flux-font-awesome.output_path');

        if (! File::isDirectory($iconPath)) {
            File::makeDirectory($iconPath, 0755, true);
            $this->components->task('Created icon directory', fn (): true => true);
        } else {
            $this->components->task('Icon directory exists');
        }
    }

    protected function checkFontAwesome(): void
    {
        $configPath = config('flux-font-awesome.source_path');

        if ($this->pathResolver->isInstalled($configPath)) {
            $resolvedPath = $this->pathResolver->resolve($configPath);
            $this->components->task('Font Awesome found at: '.$resolvedPath);
        } else {
            $this->components->warn('Font Awesome not found');
            $this->line('');
            $this->line('Install Font Awesome Free via npm:');
            $this->line('  <fg=cyan>npm install @fortawesome/fontawesome-free</>');
            $this->line('');
            $this->line('Or via composer:');
            $this->line('  <fg=cyan>composer require fortawesome/font-awesome</>');
            $this->line('');
            $this->line('For Pro:');
            $this->line('  <fg=cyan>npm install @fortawesome/fontawesome-pro</>');
            $this->line('');
            $this->line('For custom paths, set FA_SOURCE_PATH in .env');
        }
    }

    protected function publishGuidelines(): void
    {
        // Check if Boost is installed by looking for its service provider
        if (! class_exists(\Boost\Boost\BoostServiceProvider::class)) {
            return;
        }

        $guidelinesPath = base_path('.ai/guidelines/flux-font-awesome.md');
        $force = $this->option('force');

        if (File::exists($guidelinesPath) && ! $force) {
            $this->components->task('AI guidelines already exist');

            return;
        }

        $this->call('vendor:publish', [
            '--tag' => 'flux-font-awesome-guidelines',
            '--force' => $force,
        ]);

        $this->components->task('Published AI guidelines for Boost');
    }

    protected function displayNextSteps(): void
    {
        $this->line('<fg=gray>Next steps:</>');
        $this->line('');
        $this->line('  1. Build icons:');
        $this->line('     <fg=yellow>php artisan flux-fa:build</>');
        $this->line('');
        $this->line('  2. Use in Blade templates:');
        $this->line('     <fg=cyan><flux:icon.fa-user variant="solid" /></>');
        $this->line('');
        $this->line('Tip: Font Awesome is auto-detected in node_modules or vendor');
        $this->line('');
    }
}
