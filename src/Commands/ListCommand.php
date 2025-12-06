<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Support\Variant;

class ListCommand extends Command
{
    protected $signature = 'flux-fa:list
                            {--json : Output as JSON}
                            {--with-variants : Show which variants each icon has}
                            {--available : Show icons available in Font Awesome source (not yet built)}';

    protected $description = 'List Flux Font Awesome icons';

    public function __construct(
        protected IconWriter $writer,
        protected FontAwesomePathResolver $pathResolver,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $outputPath = config('flux-font-awesome.output_path');
        $builtIcons = $this->writer->getIconFiles($outputPath);

        if ($this->option('available')) {
            return $this->showAvailableIcons($builtIcons);
        }

        if (empty($builtIcons)) {
            if ($this->option('json')) {
                $this->line(json_encode(['icons' => [], 'count' => 0], JSON_PRETTY_PRINT));
            } else {
                $this->components->warn('No icons found in output directory');
                $this->newLine();
                $this->line('Run <fg=cyan>php artisan flux-fa:build</> to build icons');
            }

            return self::SUCCESS;
        }

        if ($this->option('with-variants')) {
            return $this->listWithVariants($builtIcons, $outputPath);
        }

        return $this->listSimple($builtIcons);
    }

    protected function listSimple(array $icons): int
    {
        sort($icons);

        if ($this->option('json')) {
            $this->line(json_encode([
                'icons' => $icons,
                'count' => count($icons),
            ], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        foreach ($icons as $icon) {
            $this->line("fa-{$icon}");
        }

        $this->newLine();
        $this->components->info(count($icons).' icons');

        return self::SUCCESS;
    }

    protected function listWithVariants(array $icons, string $outputPath): int
    {
        $iconsWithVariants = [];

        foreach ($icons as $iconName) {
            $variants = $this->extractVariantsFromFile($iconName, $outputPath);
            $iconsWithVariants[$iconName] = $variants;
        }

        ksort($iconsWithVariants);

        if ($this->option('json')) {
            $output = [
                'icons' => array_map(fn ($name, $variants) => [
                    'name' => $name,
                    'variants' => $variants,
                ], array_keys($iconsWithVariants), array_values($iconsWithVariants)),
                'count' => count($iconsWithVariants),
            ];

            $this->line(json_encode($output, JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        foreach ($iconsWithVariants as $iconName => $variants) {
            $variantList = implode(', ', $variants);
            $this->line("fa-{$iconName} <fg=gray>[{$variantList}]</>");
        }

        $this->newLine();
        $this->components->info(count($iconsWithVariants).' icons');

        return self::SUCCESS;
    }

    protected function showAvailableIcons(array $builtIcons): int
    {
        $configPath = config('flux-font-awesome.source_path');

        if (! $this->pathResolver->isInstalled($configPath)) {
            $this->components->error('Font Awesome installation not found');

            return self::FAILURE;
        }

        $sourcePath = $this->pathResolver->resolve($configPath);
        $availableIcons = $this->scanAvailableIcons($sourcePath);
        $notBuilt = array_diff($availableIcons, $builtIcons);

        sort($notBuilt);

        if ($this->option('json')) {
            $this->line(json_encode([
                'available' => $notBuilt,
                'count' => count($notBuilt),
                'total_in_source' => count($availableIcons),
                'already_built' => count($builtIcons),
            ], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        if (empty($notBuilt)) {
            $this->components->info('All available icons have been built');

            return self::SUCCESS;
        }

        foreach ($notBuilt as $icon) {
            $this->line($icon);
        }

        $this->newLine();
        $this->components->info(sprintf(
            '%d available icons not yet built (%d total in source, %d already built)',
            count($notBuilt),
            count($availableIcons),
            count($builtIcons)
        ));

        return self::SUCCESS;
    }

    protected function extractVariantsFromFile(string $iconName, string $outputPath): array
    {
        $filePath = sprintf('%s/fa-%s.blade.php', rtrim($outputPath, '/'), $iconName);

        if (! File::exists($filePath)) {
            return [];
        }

        $content = File::get($filePath);
        $variants = [];

        // Parse @case('variant') statements from the blade file
        if (preg_match_all("/@case\(['\"]([^'\"]+)['\"]\)/", $content, $matches)) {
            $variants = $matches[1];
        }

        return $variants;
    }

    protected function scanAvailableIcons(string $sourcePath): array
    {
        $icons = [];
        $svgsPath = $sourcePath.'/svgs';

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
}
