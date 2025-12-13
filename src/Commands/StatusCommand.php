<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Style;

class StatusCommand extends Command
{
    protected $signature = 'flux-fa:status
                            {--drift : Show detailed drift information for each icon}';

    protected $description = 'Check Flux Font Awesome installation status';

    /** @var array<string> */
    protected array $issues = [];

    /** @var array<string, array{expected: array<string>, actual: array<string>}> */
    protected array $driftIcons = [];

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
        $this->checkConfigDrift();

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

        // Show available styles
        $styles = $this->getAvailableStyles($sourcePath);
        if ($styles !== []) {
            $this->line('  <fg=gray>Styles:</> '.implode(', ', $styles));
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

    protected function checkConfigDrift(): void
    {
        $outputPath = config('flux-font-awesome.output_path');
        $icons = $this->writer->getIconFiles($outputPath);

        if ($icons === []) {
            return;
        }

        $this->driftIcons = [];

        foreach ($icons as $iconName) {
            $expectedStyles = $this->getExpectedStylesForIcon($iconName);
            $actualStyles = $this->writer->getIconStyles($iconName, $outputPath);

            sort($expectedStyles);
            sort($actualStyles);

            if ($expectedStyles !== $actualStyles) {
                $this->driftIcons[$iconName] = [
                    'expected' => $expectedStyles,
                    'actual' => $actualStyles,
                ];
            }
        }

        if ($this->driftIcons === []) {
            $this->line('<fg=green>✓</> Config: <fg=gray>All icons match config</>');

            return;
        }

        $driftCount = count($this->driftIcons);
        $this->line("<fg=yellow>○</> Config: <fg=yellow>{$driftCount} icon(s) differ from config</>");

        if ($this->option('drift')) {
            $this->newLine();
            foreach ($this->driftIcons as $iconName => $drift) {
                $expected = implode(', ', $drift['expected']) ?: '(none available)';
                $actual = implode(', ', $drift['actual']);
                $this->line("  <fg=yellow>⚠</> {$iconName}");
                $this->line("      <fg=gray>Config:</> {$expected}");
                $this->line("      <fg=gray>Built:</>  {$actual}");
            }
        } else {
            $this->line('  <fg=gray>Run with --drift to see details</>');
        }

        $this->line('  <fg=gray>Run</> <fg=cyan>php artisan flux-fa:rebuild</> <fg=gray>to sync</>');
    }

    /**
     * Get the expected styles for an icon based on config.
     *
     * @return array<string>
     */
    protected function getExpectedStylesForIcon(string $iconName): array
    {
        // Brands icons only exist in the brands style
        if ($this->isBrandsIcon($iconName)) {
            return [Style::FREE_BRANDS];
        }

        // Check for per-icon overrides first
        /** @var array<string, array<string>> $overrides */
        $overrides = config('flux-font-awesome.build.overrides', []);

        if (isset($overrides[$iconName])) {
            return $this->filterAvailableStyles($iconName, $overrides[$iconName]);
        }

        // Fall back to default styles
        /** @var array<string> $configStyles */
        $configStyles = config('flux-font-awesome.build.styles', []);

        if ($configStyles !== []) {
            return $this->filterAvailableStyles($iconName, $configStyles);
        }

        // All styles
        return $this->filterAvailableStyles($iconName, Style::ALL);
    }

    /**
     * Check if an icon is a brands-only icon.
     */
    protected function isBrandsIcon(string $iconName): bool
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            return false;
        }

        $sourcePath = $this->pathResolver->resolve($configPath);
        $brandsPath = sprintf('%s/svgs/brands/%s.svg', $sourcePath, $iconName);

        return File::exists($brandsPath);
    }

    /**
     * Filter styles to only those that exist for this icon in Font Awesome source.
     *
     * @param array<string> $styles
     *
     * @return array<string>
     */
    protected function filterAvailableStyles(string $iconName, array $styles): array
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            return [];
        }

        $sourcePath = $this->pathResolver->resolve($configPath);
        $svgsPath = $sourcePath.'/svgs';
        $available = [];

        foreach ($styles as $style) {
            $styleDir = Style::toDirectory($style);
            $svgPath = sprintf('%s/%s/%s.svg', $svgsPath, $styleDir, $iconName);

            if (File::exists($svgPath)) {
                $available[] = $style;
            }
        }

        return $available;
    }

    protected function detectEdition(string $sourcePath): string
    {
        // Check for Pro-only style directories
        $svgsPath = $sourcePath.'/svgs';

        foreach (Style::PRO as $style) {
            $styleDir = $svgsPath.'/'.Style::toDirectory($style);
            if (File::isDirectory($styleDir)) {
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
    protected function getAvailableStyles(string $sourcePath): array
    {
        $svgsPath = $sourcePath.'/svgs';
        $available = [];

        foreach (Style::ALL as $style) {
            $styleDir = $svgsPath.'/'.Style::toDirectory($style);
            if (File::isDirectory($styleDir)) {
                $available[] = $style;
            }
        }

        return $available;
    }
}
