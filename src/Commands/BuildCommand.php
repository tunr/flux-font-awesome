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
                            {icons* : Icons to build (e.g., user github)}
                            {--variant=* : Specific variants to build (e.g., solid regular)}';

    protected $description = 'Build Flux Font Awesome icon components';

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

        if (! $this->validatePaths()) {
            return self::FAILURE;
        }

        $iconsToBuild = $this->argument('icons');
        $variantsToBuild = $this->getVariantsToBuild();

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

        return Variant::ALL;
    }

    protected function buildIcons(array $icons, array $variants): void
    {
        $sourcePath = $this->pathResolver->resolve(config('flux-font-awesome.source_path'));
        $outputPath = config('flux-font-awesome.output_path');

        $progressBar = $this->output->createProgressBar(count($icons));
        $progressBar->start();

        foreach ($icons as $iconName) {
            $variantSvgPaths = $this->findIconVariants($sourcePath, $iconName, $variants);

            if (empty($variantSvgPaths)) {
                if ($this->output->isVerbose()) {
                    $this->newLine();
                    $this->components->warn("Icon '{$iconName}' not found in Font Awesome source");
                }
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
