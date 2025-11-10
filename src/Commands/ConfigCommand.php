<?php

namespace Tunr\FluxFontAwesome\Commands;

use Illuminate\Console\Command;
use Tunr\FluxFontAwesome\FluxFontAwesomeServiceProvider;

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
            'Output Path' => resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH),
        ]);

        $this->displaySection('Defaults', [
            'Default Variant' => config('flux-font-awesome.default_variant'),
            'Default Size' => config('flux-font-awesome.default_size'),
            'Icon Scale' => config('flux-font-awesome.icon_scale'),
        ]);

        $this->displaySection('Sizes', config('flux-font-awesome.sizes'));

        $this->displaySection('Build Settings', [
            'Include Icons' => $this->formatArray(config('flux-font-awesome.build.include')),
            'Exclude Icons' => $this->formatArray(config('flux-font-awesome.build.exclude')),
        ]);

        return self::SUCCESS;
    }

    protected function displaySection(string $title, array $items): void
    {
        $this->line(sprintf('<fg=yellow>%s:</>', $title));
        $this->newLine();

        $maxKeyLength = max(array_map(strlen(...), array_keys($items)));

        foreach ($items as $key => $value) {
            $paddedKey = str_pad((string) $key, $maxKeyLength);
            $this->line(sprintf('  <fg=gray>%s:</> <fg=cyan>%s</>', $paddedKey, $value));
        }

        $this->newLine();
    }

    protected function formatArray(?array $array): string
    {
        if ($array === null || $array === []) {
            return '<fg=gray>(all)</>';
        }

        return implode(', ', $array);
    }
}
