<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'fa:install
                            {--force : Overwrite existing configuration}';

    protected $description = 'Install Flux Font Awesome package';

    public function handle(): int
    {
        $this->components->info('Installing Flux Font Awesome...');

        // Publish configuration
        $this->publishConfiguration();

        // Create icon directory
        $this->createIconDirectory();

        // Check Font Awesome installation
        $this->checkFontAwesome();

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
        $iconPath = config('flux-font-awesome.output_path', resource_path('views/flux/icon/fa'));

        if (! File::isDirectory($iconPath)) {
            File::makeDirectory($iconPath, 0755, true);
            $this->components->task('Created icon directory', fn () => true);
        } else {
            $this->components->task('Icon directory exists');
        }
    }

    protected function checkFontAwesome(): void
    {
        $sourcePath = config('flux-font-awesome.source_path', base_path('node_modules/@fortawesome/fontawesome-free'));

        if (File::isDirectory($sourcePath)) {
            $this->components->task('Font Awesome found at: ' . $sourcePath);
        } else {
            $this->components->warn('Font Awesome not found at: ' . $sourcePath);
            $this->line('');
            $this->line('Install Font Awesome Free:');
            $this->line('  npm install @fortawesome/fontawesome-free');
            $this->line('');
            $this->line('Or update FA_SOURCE_PATH in your .env file to point to your Font Awesome installation.');
        }
    }

    protected function displayNextSteps(): void
    {
        $this->line('<fg=gray>Next steps:</>');
        $this->line('');
        $this->line('  1. Configure source path in .env:');
        $this->line('     <fg=yellow>FA_SOURCE_PATH=</><fg=cyan>node_modules/@fortawesome/fontawesome-free</>');
        $this->line('');
        $this->line('  2. Build icons:');
        $this->line('     <fg=yellow>php artisan fa:build</>');
        $this->line('');
        $this->line('  3. Use in Blade templates:');
        $this->line('     <fg=cyan><flux:icon.fa.user variant="solid" /></>');
        $this->line('');
    }
}
