<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Variant;

class StatusCommand extends Command
{
    protected $signature = 'flux-fa:status';

    protected $description = 'Check Flux Font Awesome installation status';

    /** @var array<string> */
    protected array $issues = [];

    public function __construct(
        protected IconWriter $writer,
        protected FontAwesomePathResolver $pathResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->newLine();

        $this->checkFontAwesome();
        $this->checkOutputDirectory();
        $this->checkBuiltIcons();

        $this->newLine();

        if ($this->issues !== []) {
            $this->components->error(count($this->issues).' issue(s) found');
            $this->newLine();

            foreach ($this->issues as $issue) {
                $this->line("  <fg=red>•</> {$issue}");
            }

            $this->newLine();

            return self::FAILURE;
        }

        $this->components->info('No issues found');

        return self::SUCCESS;
    }

    protected function checkFontAwesome(): void
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            $this->line('<fg=red>✗</> Font Awesome: <fg=red>Not installed</>');
            $this->issues[] = 'Font Awesome not found. Run: npm install @fortawesome/fontawesome-free';

            return;
        }

        $sourcePath = $this->pathResolver->resolve($configPath);
        $edition = $this->detectEdition($sourcePath);
        $version = $this->detectVersion($sourcePath);

        $editionLabel = $edition === 'pro' ? '<fg=yellow>Pro</>' : '<fg=green>Free</>';
        $versionLabel = $version ? " <fg=gray>({$version})</>" : '';

        $this->line("<fg=green>✓</> Font Awesome: {$editionLabel}{$versionLabel}");

        // Show available variants
        $variants = $this->getAvailableVariants($sourcePath);
        if ($variants !== []) {
            $this->line('  <fg=gray>Variants:</> '.implode(', ', $variants));
        }
    }

    protected function checkOutputDirectory(): void
    {
        $outputPath = config('flux-font-awesome.output_path');

        if (! File::isDirectory($outputPath)) {
            $this->line("<fg=yellow>○</> Output: <fg=gray>{$outputPath}</> <fg=yellow>(not created)</>");

            return;
        }

        if (! is_writable($outputPath)) {
            $this->line("<fg=red>✗</> Output: <fg=gray>{$outputPath}</> <fg=red>(not writable)</>");
            $this->issues[] = "Output directory is not writable: {$outputPath}";

            return;
        }

        $this->line("<fg=green>✓</> Output: <fg=gray>{$outputPath}</>");
    }

    protected function checkBuiltIcons(): void
    {
        $outputPath = config('flux-font-awesome.output_path');
        $icons = $this->writer->getIconFiles($outputPath);
        $count = count($icons);

        if ($count === 0) {
            $this->line('<fg=yellow>○</> Icons: <fg=yellow>None built</>');

            return;
        }

        $this->line("<fg=green>✓</> Icons: <fg=cyan>{$count}</> built");
    }

    protected function detectEdition(string $sourcePath): string
    {
        // Check for Pro-only variant directories
        $svgsPath = $sourcePath.'/svgs';

        foreach (Variant::PRO as $variant) {
            $variantDir = $svgsPath.'/'.Variant::toDirectory($variant);
            if (File::isDirectory($variantDir)) {
                return 'pro';
            }
        }

        return 'free';
    }

    protected function detectVersion(string $sourcePath): ?string
    {
        $packageJsonPath = $sourcePath.'/package.json';

        if (! File::exists($packageJsonPath)) {
            return null;
        }

        $packageJson = json_decode(File::get($packageJsonPath), true);

        return $packageJson['version'] ?? null;
    }

    /**
     * @return array<string>
     */
    protected function getAvailableVariants(string $sourcePath): array
    {
        $svgsPath = $sourcePath.'/svgs';
        $available = [];

        foreach (Variant::ALL as $variant) {
            $variantDir = $svgsPath.'/'.Variant::toDirectory($variant);
            if (File::isDirectory($variantDir)) {
                $available[] = $variant;
            }
        }

        return $available;
    }
}
