<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Style;

class BuildCommand extends Command
{
    protected $signature = 'flux-fa:build
                            {icons* : Icons to build (e.g., user github)}
                            {--style=* : Specific styles to build (e.g., solid regular)}';

    protected $description = 'Build Flux Font Awesome icon components';

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
        $this->components->info('Building Font Awesome icons...');
        $this->newLine();

        if (! $this->validatePaths()) {
            return self::FAILURE;
        }

        /** @var array<string> $iconsToBuild */
        $iconsToBuild = $this->argument('icons');
        $stylesToBuild = $this->getStylesToBuild();

        $this->buildIcons($iconsToBuild, $stylesToBuild);

        $this->newLine();
        $this->components->info("Built {$this->iconCount} icons with {$this->styleCount} total styles");

        if ($this->failedIcons !== []) {
            $this->newLine();
            $this->components->warn(count($this->failedIcons).' icon(s) failed to build:');
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
     * @return array<string>
     */
    protected function getStylesToBuild(): array
    {
        /** @var array<string> $requestedStyles */
        $requestedStyles = $this->option('style');

        if ($requestedStyles !== []) {
            return array_map(Style::normalize(...), $requestedStyles);
        }

        /** @var array<string> $configStyles */
        $configStyles = config('flux-font-awesome.build.styles', []);
        if ($configStyles !== []) {
            return $configStyles;
        }

        return Style::ALL;
    }

    /**
     * @param array<string> $icons
     * @param array<string> $styles
     */
    protected function buildIcons(array $icons, array $styles): void
    {
        $sourcePath = $this->pathResolver->resolve(config('flux-font-awesome.source_path'));
        $outputPath = config('flux-font-awesome.output_path');

        $progressBar = $this->output->createProgressBar(count($icons));
        $progressBar->start();

        foreach ($icons as $iconName) {
            // Brands icons only exist in the brands style
            $iconStyles = $this->isBrandsIcon($sourcePath, $iconName)
                ? [Style::FREE_BRANDS]
                : $styles;

            $styleSvgPaths = $this->findIconStyles($sourcePath, $iconName, $iconStyles);

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
     * Check if an icon is a brands-only icon.
     */
    protected function isBrandsIcon(string $sourcePath, string $iconName): bool
    {
        $brandsPath = sprintf('%s/svgs/brands/%s.svg', $sourcePath, $iconName);

        return File::exists($brandsPath);
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
