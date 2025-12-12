<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Style;

class RebuildCommand extends Command
{
    protected $signature = 'flux-fa:rebuild
                            {icons?* : Specific icons to rebuild (default: all existing icons)}';

    protected $description = 'Rebuild existing Font Awesome icons using current config';

    protected int $iconCount = 0;

    protected int $styleCount = 0;

    /** @var array<string, string> */
    protected array $failedIcons = [];

    public function __construct(
        protected IconWriter $writer,
        protected FontAwesomePathResolver $pathResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->validatePaths()) {
            return self::FAILURE;
        }

        $outputPath = config('flux-font-awesome.output_path');

        /** @var array<string> $icons */
        $icons = $this->argument('icons');

        if ($icons === []) {
            $icons = $this->writer->getIconFiles($outputPath);
        }

        if ($icons === []) {
            $this->components->warn('No icons to rebuild');
            $this->newLine();
            $this->line('Run <fg=cyan>php artisan flux-fa:build user github</> to build icons first');

            return self::SUCCESS;
        }

        $this->components->info('Rebuilding '.count($icons).' Font Awesome icons...');
        $this->newLine();

        $this->rebuildIcons($icons);

        $this->newLine();
        $this->components->info("Rebuilt {$this->iconCount} icons with {$this->styleCount} total styles");

        if ($this->failedIcons !== []) {
            $this->newLine();
            $this->components->warn(count($this->failedIcons).' icon(s) failed to rebuild:');
            foreach ($this->failedIcons as $iconName => $error) {
                $this->line("  <fg=red>✗</> {$iconName}: {$error}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function validatePaths(): bool
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            $this->components->error('Font Awesome installation not found');
            $this->newLine();
            $this->line('Install Font Awesome via npm:');
            $this->line('  <fg=cyan>npm install @fortawesome/fontawesome-free</>');
            $this->newLine();
            $this->line('Or set a custom path in .env:');
            $this->line('  <fg=cyan>FA_SOURCE_PATH=/path/to/fontawesome</>');

            return false;
        }

        return true;
    }

    /**
     * @param array<string> $icons
     */
    protected function rebuildIcons(array $icons): void
    {
        $sourcePath = $this->pathResolver->resolve(config('flux-font-awesome.source_path'));
        $outputPath = config('flux-font-awesome.output_path');

        $progressBar = $this->output->createProgressBar(count($icons));
        $progressBar->start();

        foreach ($icons as $iconName) {
            $styles = $this->getStylesForIcon($iconName);
            $styleSvgPaths = $this->findIconStyles($sourcePath, $iconName, $styles);

            if ($styleSvgPaths === []) {
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->components->warn("Icon '{$iconName}' not found in Font Awesome source");
                }
                $progressBar->advance();

                continue;
            }

            try {
                $this->writer->write($iconName, $styleSvgPaths, $outputPath);
                $this->iconCount++;
                $this->styleCount += count($styleSvgPaths);
            } catch (\RuntimeException $e) {
                $this->failedIcons[$iconName] = $e->getMessage();
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Get the styles to build for a specific icon.
     *
     * @return array<string>
     */
    protected function getStylesForIcon(string $iconName): array
    {
        // Check for per-icon overrides first
        /** @var array<string, array<string>> $overrides */
        $overrides = config('flux-font-awesome.build.overrides', []);

        if (isset($overrides[$iconName])) {
            return $overrides[$iconName];
        }

        // Fall back to default styles
        /** @var array<string> $configStyles */
        $configStyles = config('flux-font-awesome.build.styles', []);

        if ($configStyles !== []) {
            return $configStyles;
        }

        return Style::ALL;
    }

    /**
     * @param array<string> $styles
     *
     * @return array<string, string>
     */
    protected function findIconStyles(string $sourcePath, string $iconName, array $styles): array
    {
        $svgsPath = $sourcePath.'/svgs';
        $found = [];

        foreach ($styles as $style) {
            $styleDir = Style::toDirectory($style);
            $svgPath = sprintf('%s/%s/%s.svg', $svgsPath, $styleDir, $iconName);

            if (File::exists($svgPath)) {
                $found[$style] = $svgPath;
            }
        }

        return $found;
    }
}
