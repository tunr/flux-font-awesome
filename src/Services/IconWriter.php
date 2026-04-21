<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Services;

use Illuminate\Filesystem\Filesystem;
use Tunr\FluxFontAwesome\Support\IconName;

class IconWriter
{
    public function __construct(
        protected Filesystem $files,
        protected SvgParser $parser,
    ) {}

    /**
     * Write a single icon with all its styles to a Blade component.
     *
     * @param array<string, string> $styleSvgPaths
     */
    public function write(string $iconName, array $styleSvgPaths, string $outputPath): bool
    {
        $iconContent = $this->generateIconContent($iconName, $styleSvgPaths);
        $filePath = $this->getIconFilePath($iconName, $outputPath);

        $this->ensureDirectoryExists(dirname($filePath));

        return (bool) $this->files->put($filePath, $iconContent);
    }

    /**
     * Generate the Blade component content for an icon with multiple styles.
     *
     * @param array<string, string> $styleSvgPaths
     */
    protected function generateIconContent(string $iconName, array $styleSvgPaths): string
    {
        $styles = [];

        foreach ($styleSvgPaths as $style => $svgPath) {
            if (! $this->files->exists($svgPath)) {
                continue;
            }

            $svgContent = $this->files->get($svgPath);
            $parsed = $this->parser->parse($svgContent);
            $pathMarkup = $this->parser->generatePathMarkup($parsed);

            $styles[$style] = [
                'viewBox' => $parsed['viewBox'],
                'paths' => $pathMarkup,
            ];
        }

        if ($styles === []) {
            throw new \RuntimeException("No valid styles found for icon: {$iconName}");
        }

        return $this->generateBladeTemplate($iconName, $styles);
    }

    /**
     * Generate the complete Blade template for a multi-style icon.
     *
     * @param array<string, array{viewBox: string, paths: string}> $styles
     */
    protected function generateBladeTemplate(string $iconName, array $styles): string
    {
        $stubPath = __DIR__.'/../../stubs/icon.blade.stub';

        if (! $this->files->exists($stubPath)) {
            throw new \RuntimeException("Icon template stub not found at: {$stubPath}");
        }

        $template = $this->files->get($stubPath);
        $styleCases = $this->generateStyleCases($styles);
        $viewBoxResolver = $this->generateViewBoxResolver($styles);
        $defaultCase = $this->generateDefaultCase($styles);

        return str_replace(
            ['{{VIEW_BOX_RESOLVER}}', '{{VARIANT_CASES}}', '{{DEFAULT_CASE}}'],
            [$viewBoxResolver, $styleCases, $defaultCase],
            $template
        );
    }

    /**
     * Generate @case blocks for each style.
     *
     * @param array<string, array{viewBox: string, paths: string}> $styles
     */
    protected function generateStyleCases(array $styles): string
    {
        $cases = [];

        foreach ($styles as $style => $data) {
            $cases[] = <<<CASE
        @case('{$style}')
            {$data['paths']}
            @break
CASE;
        }

        return implode("\n\n", $cases);
    }

    /**
     * Generate the PHP expression that resolves the correct viewBox for the active style.
     *
     * When all styles share the same viewBox, generates a simple string assignment.
     * When styles differ, generates a match expression to resolve per-style.
     *
     * @param array<string, array{viewBox: string, paths: string}> $styles
     */
    protected function generateViewBoxResolver(array $styles): string
    {
        $viewBoxes = array_map(fn (array $data): string => $data['viewBox'], $styles);
        $unique = array_unique($viewBoxes);

        if (count($unique) === 1) {
            return sprintf("\$viewBox = '%s';", reset($unique));
        }

        // Find most common viewBox for the default arm
        $counts = array_count_values($viewBoxes);
        arsort($counts);
        $defaultViewBox = array_key_first($counts);

        $arms = [];
        foreach ($styles as $style => $data) {
            if ($data['viewBox'] !== $defaultViewBox) {
                $arms[] = sprintf("        '%s' => '%s',", $style, $data['viewBox']);
            }
        }
        $arms[] = sprintf("        default => '%s',", $defaultViewBox);

        return "\$viewBox = match(\$style) {\n".implode("\n", $arms)."\n    };";
    }

    /**
     * Generate the default case (uses configured default style).
     *
     * @param array<string, array{viewBox: string, paths: string}> $styles
     */
    protected function generateDefaultCase(array $styles): string
    {
        // Try to use the configured default style
        $defaultStyle = config('flux-font-awesome.default_style', 'solid');

        if (isset($styles[$defaultStyle])) {
            return "            {$styles[$defaultStyle]['paths']}";
        }

        // Fallback to first available style
        $firstStyle = reset($styles);

        if ($firstStyle === false) {
            return '';
        }

        return "            {$firstStyle['paths']}";
    }

    /**
     * Get the file path for an icon component.
     *
     * @throws \InvalidArgumentException If icon name is invalid
     */
    protected function getIconFilePath(string $iconName, string $outputPath): string
    {
        // Validate icon name to prevent path traversal
        $validatedName = IconName::validate($iconName);

        return sprintf('%s/fa-%s.blade.php', rtrim($outputPath, '/'), $validatedName);
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
     *
     * @return array<string>
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

    /**
     * Get the styles present in an existing icon file.
     *
     * @return array<string>
     */
    public function getIconStyles(string $iconName, string $outputPath): array
    {
        $filePath = $this->getIconFilePath($iconName, $outputPath);

        if (! $this->files->exists($filePath)) {
            return [];
        }

        $content = $this->files->get($filePath);

        // Parse @case('style') statements from the blade file
        preg_match_all("/@case\('([^']+)'\)/", $content, $matches);

        return $matches[1];
    }
}
