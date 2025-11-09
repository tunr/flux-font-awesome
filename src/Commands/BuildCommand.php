<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Variant;

class BuildCommand extends Command
{
    protected $signature = 'flux-fa:build
                            {icons?* : Specific icons to build (e.g., user github)}
                            {--variant=* : Specific variants to build (e.g., solid regular)}
                            {--refresh : Rebuild all icons from scratch}
                            {--clean : Remove all existing icons before building}';

    protected $description = 'Build Font Awesome icon components';

    protected int $iconCount = 0;

    protected int $variantCount = 0;

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

        // Validate paths
        if (! $this->validatePaths()) {
            return self::FAILURE;
        }

        // Clean if requested
        if ($this->option('clean')) {
            $this->cleanIcons();
        }

        // Determine which icons and variants to build
        $iconsToBuild = $this->getIconsToBuild();
        $variantsToBuild = $this->getVariantsToBuild();

        if (empty($iconsToBuild)) {
            $this->components->error('No icons found to build');

            return self::FAILURE;
        }

        // Build icons
        $this->buildIcons($iconsToBuild, $variantsToBuild);

        $this->newLine();
        $this->components->info("Built {$this->iconCount} icons with {$this->variantCount} total variants");

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

    protected function cleanIcons(): void
    {
        $outputPath = config('flux-font-awesome.output_path');

        if (File::isDirectory($outputPath)) {
            $files = File::files($outputPath);
            $count = count($files);

            if ($count > 0) {
                File::cleanDirectory($outputPath);
                $this->components->task("Cleaned {$count} existing icons");
            }
        }
    }

    protected function getIconsToBuild(): array
    {
        $requestedIcons = $this->argument('icons');

        // If specific icons requested, validate they exist
        if (! empty($requestedIcons)) {
            return $requestedIcons;
        }

        // Get icons from config include list
        $configInclude = config('flux-font-awesome.build.include', []);
        if (! empty($configInclude)) {
            return $configInclude;
        }

        // Otherwise, scan for all available icons
        $sourcePath = $this->pathResolver->resolve(config('flux-font-awesome.source_path'));

        return $this->scanAvailableIcons($sourcePath);
    }

    protected function scanAvailableIcons(string $sourcePath): array
    {
        $icons = [];
        $svgsPath = $sourcePath.'/svgs';

        // Scan each variant directory
        foreach (Variant::ALL as $variant) {
            $variantDir = $svgsPath.'/'.Variant::toDirectory($variant);

            if (! File::isDirectory($variantDir)) {
                continue;
            }

            $files = File::files($variantDir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'svg') {
                    $iconName = $file->getFilenameWithoutExtension();
                    $icons[$iconName] = true;
                }
            }
        }

        return array_keys($icons);
    }

    protected function getVariantsToBuild(): array
    {
        $requestedVariants = $this->option('variant');

        if (! empty($requestedVariants)) {
            return array_map(fn ($v) => Variant::normalize($v), $requestedVariants);
        }

        $configVariants = config('flux-font-awesome.build.variants', []);
        if (! empty($configVariants)) {
            return $configVariants;
        }

        // Build all available variants
        return Variant::ALL;
    }

    protected function buildIcons(array $icons, array $variants): void
    {
        $sourcePath = $this->pathResolver->resolve(config('flux-font-awesome.source_path'));
        $outputPath = config('flux-font-awesome.output_path');
        $exclude = config('flux-font-awesome.build.exclude', []);

        $progressBar = $this->output->createProgressBar(count($icons));
        $progressBar->start();

        foreach ($icons as $iconName) {
            // Skip excluded icons
            if (in_array($iconName, $exclude)) {
                $progressBar->advance();
                continue;
            }

            // Find all available variants for this icon
            $variantSvgPaths = $this->findIconVariants($sourcePath, $iconName, $variants);

            if (empty($variantSvgPaths)) {
                $progressBar->advance();
                continue;
            }

            try {
                $this->writer->write($iconName, $variantSvgPaths, $outputPath);
                $this->iconCount++;
                $this->variantCount += count($variantSvgPaths);
            } catch (\Exception $e) {
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->components->error("Failed to build {$iconName}: {$e->getMessage()}");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    protected function findIconVariants(string $sourcePath, string $iconName, array $variants): array
    {
        $svgsPath = $sourcePath.'/svgs';
        $found = [];

        foreach ($variants as $variant) {
            $variantDir = Variant::toDirectory($variant);
            $svgPath = sprintf('%s/%s/%s.svg', $svgsPath, $variantDir, $iconName);

            if (File::exists($svgPath)) {
                $found[$variant] = $svgPath;
            }
        }

        return $found;
    }
}
