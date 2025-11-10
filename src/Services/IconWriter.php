<?php

namespace Tunr\FluxFontAwesome\Services;

use Illuminate\Filesystem\Filesystem;
use Tunr\FluxFontAwesome\Support\Variant;

class IconWriter
{
    public function __construct(
        protected Filesystem $files,
        protected SvgParser $parser,
    ) {}

    /**
     * Write a single icon with all its variants to a Blade component.
     */
    public function write(string $iconName, array $variantSvgPaths, string $outputPath): bool
    {
        $iconContent = $this->generateIconContent($iconName, $variantSvgPaths);
        $filePath = $this->getIconFilePath($iconName, $outputPath);

        $this->ensureDirectoryExists(dirname($filePath));

        return (bool) $this->files->put($filePath, $iconContent);
    }

    /**
     * Generate the Blade component content for an icon with multiple variants.
     */
    protected function generateIconContent(string $iconName, array $variantSvgPaths): string
    {
        $variants = [];

        foreach ($variantSvgPaths as $variant => $svgPath) {
            if (! $this->files->exists($svgPath)) {
                continue;
            }

            $svgContent = $this->files->get($svgPath);
            $parsed = $this->parser->parse($svgContent);
            $pathMarkup = $this->parser->generatePathMarkup($parsed);

            $variants[$variant] = [
                'viewBox' => $parsed['viewBox'],
                'paths' => $pathMarkup,
            ];
        }

        if ($variants === []) {
            throw new \RuntimeException('No valid variants found for icon: '.$iconName);
        }

        return $this->generateBladeTemplate($iconName, $variants);
    }

    /**
     * Generate the complete Blade template for a multi-variant icon.
     */
    protected function generateBladeTemplate(string $iconName, array $variants): string
    {
        $stubPath = __DIR__.'/../../stubs/icon.blade.stub';

        if (! $this->files->exists($stubPath)) {
            throw new \RuntimeException('Icon template stub not found at: '.$stubPath);
        }

        $template = $this->files->get($stubPath);
        $variantCases = $this->generateVariantCases($variants);
        $viewBox = $this->getCommonViewBox($variants);
        $defaultCase = $this->generateDefaultCase($variants);

        return str_replace(
            ['{{VIEW_BOX}}', '{{VARIANT_CASES}}', '{{DEFAULT_CASE}}'],
            [$viewBox, $variantCases, $defaultCase],
            $template
        );
    }

    /**
     * Generate @case blocks for each variant.
     */
    protected function generateVariantCases(array $variants): string
    {
        $cases = [];

        foreach ($variants as $variant => $data) {
            $cases[] = <<<CASE
        @case('{$variant}')
            {$data['paths']}
            @break
CASE;
        }

        return implode("\n\n", $cases);
    }

    /**
     * Get common viewBox from variants (FA icons typically share viewBox).
     */
    protected function getCommonViewBox(array $variants): string
    {
        // FA icons typically share viewBox across variants - just use first
        $firstVariant = reset($variants);

        return $firstVariant['viewBox'] ?? '0 0 512 512';
    }

    /**
     * Generate the default case (uses configured default style).
     */
    protected function generateDefaultCase(array $variants): string
    {
        // Try to use the configured default style
        $defaultStyle = config('flux-font-awesome.default_variant', 'solid');

        if (isset($variants[$defaultStyle])) {
            return '            '.$variants[$defaultStyle]['paths'];
        }

        // Fallback to first available variant
        $firstVariant = reset($variants);

        return '            '.$firstVariant['paths'];
    }

    /**
     * Get the file path for an icon component.
     */
    protected function getIconFilePath(string $iconName, string $outputPath): string
    {
        return sprintf('%s/fa-%s.blade.php', rtrim($outputPath, '/'), $iconName);
    }

    /**
     * Ensure a directory exists.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Delete an icon file.
     */
    public function delete(string $iconName, string $outputPath): bool
    {
        $filePath = $this->getIconFilePath($iconName, $outputPath);

        if ($this->files->exists($filePath)) {
            return $this->files->delete($filePath);
        }

        return false;
    }

    /**
     * Check if an icon file exists.
     */
    public function exists(string $iconName, string $outputPath): bool
    {
        return $this->files->exists($this->getIconFilePath($iconName, $outputPath));
    }

    /**
     * Get all icon files in the output directory.
     */
    public function getIconFiles(string $outputPath): array
    {
        if (! $this->files->isDirectory($outputPath)) {
            return [];
        }

        $files = $this->files->files($outputPath);

        return collect($files)
            ->filter(fn ($file): bool => $file->getExtension() === 'php')
            ->map(fn ($file) => $file->getBasename('.blade.php'))
            ->filter(fn ($name): bool => str_starts_with($name, 'fa-'))
            ->map(fn ($name): string => str_replace('fa-', '', $name))
            ->values()
            ->all();
    }
}
