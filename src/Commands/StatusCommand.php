<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\FluxFontAwesomeServiceProvider;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Variant;

class StatusCommand extends Command
{
    protected $signature = 'flux-fa:status';

    protected $description = 'Show Flux Font Awesome icon build status';

    public function __construct(
        protected IconWriter $writer,
        protected FontAwesomePathResolver $pathResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->components->info('Flux Font Awesome Status');
        $this->newLine();

        // Check Font Awesome installation
        $this->checkFontAwesomeInstallation();

        // Check built icons
        $this->checkBuiltIcons();

        // Check available variants
        $this->checkAvailableVariants();

        return self::SUCCESS;
    }

    protected function checkFontAwesomeInstallation(): void
    {
        $configPath = config('flux-font-awesome.source_path');

        $this->line('<fg=yellow>Font Awesome Installation:</>');
        $this->newLine();

        if ($this->pathResolver->isInstalled($configPath)) {
            $sourcePath = $this->pathResolver->resolve($configPath);
            $this->components->task("Source path found: {$sourcePath}");

            // Check for package.json to determine version
            $packageJsonPath = $sourcePath.'/package.json';
            if (File::exists($packageJsonPath)) {
                $packageJson = json_decode(File::get($packageJsonPath), true);
                $version = $packageJson['version'] ?? 'unknown';
                $this->line("  <fg=gray>Version:</> <fg=cyan>{$version}</>");
            }

            // Count available SVGs
            $svgsPath = $sourcePath.'/svgs';
            if (File::isDirectory($svgsPath)) {
                $iconCount = $this->countAvailableIcons($svgsPath);
                $this->line("  <fg=gray>Available icons:</> <fg=cyan>{$iconCount}</>");
            }
        } else {
            $this->components->error('Font Awesome not found');
            $this->line('');
            $this->line('  Run: <fg=cyan>npm install @fortawesome/fontawesome-free</>');
        }

        $this->newLine();
    }

    protected function checkBuiltIcons(): void
    {
        $outputPath = resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH);

        $this->line('<fg=yellow>Built Icons:</>');
        $this->newLine();

        if (! File::isDirectory($outputPath)) {
            $this->components->warn('No icons built yet');
            $this->line('  Run: <fg=cyan>php artisan flux-fa:build</>');
            $this->newLine();

            return;
        }

        $builtIcons = $this->writer->getIconFiles($outputPath);
        $count = count($builtIcons);

        if ($count === 0) {
            $this->components->warn('No icons built yet');
            $this->line('  Run: <fg=cyan>php artisan flux-fa:build</>');
        } else {
            $this->components->task("Built icons: {$count}");
            $this->line("  <fg=gray>Output path:</> <fg=cyan>{$outputPath}</>");

            if ($this->output->isVerbose() && $count <= 20) {
                $this->newLine();
                $this->line('  <fg=gray>Icons:</>');
                foreach ($builtIcons as $icon) {
                    $this->line("    - {$icon}");
                }
            }
        }

        $this->newLine();
    }

    protected function checkAvailableVariants(): void
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            return;
        }

        $svgsPath = $this->pathResolver->getSvgsPath($configPath);

        $this->line('<fg=yellow>Available Variants:</>');
        $this->newLine();

        $availableVariants = [];

        foreach (Variant::ALL as $variant) {
            $variantDir = $svgsPath.'/'.Variant::toDirectory($variant);

            if (File::isDirectory($variantDir)) {
                $count = count(File::files($variantDir));
                $availableVariants[$variant] = $count;
            }
        }

        if (empty($availableVariants)) {
            $this->components->warn('No variants found');
        } else {
            foreach ($availableVariants as $variant => $count) {
                $isFree = Variant::isFree($variant);
                $badge = $isFree ? '<fg=green>FREE</>' : '<fg=yellow>PRO</>';
                $this->line("  {$badge} <fg=cyan>{$variant}</>: {$count} icons");
            }
        }

        $this->newLine();
    }

    protected function countAvailableIcons(string $svgsPath): int
    {
        $icons = [];

        foreach (Variant::ALL as $variant) {
            $variantDir = $svgsPath.'/'.Variant::toDirectory($variant);

            if (! File::isDirectory($variantDir)) {
                continue;
            }

            $files = File::files($variantDir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'svg') {
                    $icons[$file->getFilenameWithoutExtension()] = true;
                }
            }
        }

        return count($icons);
    }
}
