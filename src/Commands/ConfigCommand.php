<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;

class ConfigCommand extends Command
{
    protected $signature = 'flux-fa:config';

    protected $description = 'Display current Flux Font Awesome configuration';

    public function handle(): int
    {
        $this->components->info('Flux Font Awesome Configuration');
        $this->newLine();

        $this->displaySection('Paths', [
            'Source Path' => config('flux-font-awesome.source_path'),
            'Output Path' => config('flux-font-awesome.output_path'),
        ]);

        $this->displaySection('Defaults', [
            'Default Variant' => config('flux-font-awesome.default_variant'),
            'Default Size' => config('flux-font-awesome.default_size'),
            'Fallback Strategy' => config('flux-font-awesome.fallback_strategy'),
        ]);

        $this->displaySection('Sizes', config('flux-font-awesome.sizes'));

        $this->displaySection('Build Settings', [
            'Include Icons' => $this->formatArray(config('flux-font-awesome.build.include')),
            'Exclude Icons' => $this->formatArray(config('flux-font-awesome.build.exclude')),
            'Variants' => $this->formatArray(config('flux-font-awesome.build.variants')),
            'Preserve Existing' => config('flux-font-awesome.build.preserve_existing') ? 'Yes' : 'No',
        ]);

        $this->displaySection('Duotone', [
            'Primary Opacity' => config('flux-font-awesome.duotone.primary_opacity'),
            'Secondary Opacity' => config('flux-font-awesome.duotone.secondary_opacity'),
        ]);

        return self::SUCCESS;
    }

    protected function displaySection(string $title, array $items): void
    {
        $this->line("<fg=yellow>{$title}:</>");
        $this->newLine();

        $maxKeyLength = max(array_map('strlen', array_keys($items)));

        foreach ($items as $key => $value) {
            $paddedKey = str_pad($key, $maxKeyLength);
            $this->line("  <fg=gray>{$paddedKey}:</> <fg=cyan>{$value}</>");
        }

        $this->newLine();
    }

    protected function formatArray(array $array): string
    {
        if (empty($array)) {
            return '<fg=gray>(all)</>';
        }

        return implode(', ', $array);
    }
}
