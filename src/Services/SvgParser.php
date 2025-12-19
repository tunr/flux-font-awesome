<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Services;

use Symfony\Component\DomCrawler\Crawler;

class SvgParser
{
    /**
     * Allowed attributes for path elements.
     * Only these attributes will be extracted from SVG paths for security.
     */
    private const ALLOWED_PATH_ATTRIBUTES = ['d', 'fill', 'opacity', 'fill-opacity', 'class'];

    /**
     * Parse an SVG file and extract its contents.
     *
     * @return array{viewBox: string, paths: array<array{d: string|null, fill?: string, opacity?: string, fill-opacity?: string, class?: string}>, attributes: array<string, string>}
     */
    public function parse(string $svgContent): array
    {
        $crawler = new Crawler($svgContent);
        $svg = $crawler->filter('svg')->first();

        if ($svg->count() === 0) {
            throw new \RuntimeException('Invalid SVG content: no <svg> element found');
        }

        return [
            'viewBox' => $this->extractViewBox($svg),
            'paths' => $this->extractPaths($svg),
            'attributes' => $this->extractAttributes($svg),
        ];
    }

    /**
     * Extract the viewBox attribute from an SVG element.
     */
    protected function extractViewBox(Crawler $svg): string
    {
        // Try to get viewBox attribute directly from the DOMElement
        // Note: DOM normalizes attribute names to lowercase
        $node = $svg->getNode(0);

        // Type guard: ensure we have a DOMElement
        if (! $node instanceof \DOMElement) {
            return '0 0 512 512';
        }

        $viewBox = $node->getAttribute('viewbox');

        if ($viewBox === '') {
            // Fallback to common Font Awesome viewBox
            return '0 0 512 512';
        }

        // Validate viewBox format
        if (! $this->isValidViewBox($viewBox)) {
            return '0 0 512 512';
        }

        return $viewBox;
    }

    /**
     * Validate that a viewBox string has the correct format.
     *
     * Expected format: "minX minY width height" with numeric values
     */
    protected function isValidViewBox(string $viewBox): bool
    {
        $parts = preg_split('/[\s,]+/', trim($viewBox));

        if ($parts === false || count($parts) !== 4) {
            return false;
        }

        foreach ($parts as $part) {
            if (! is_numeric($part)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract all path elements from an SVG.
     *
     * SECURITY: This method intentionally ONLY extracts <path> elements
     * and a specific allowlist of attributes to prevent XSS attacks.
     * All other SVG elements (script, animate, etc.) are ignored.
     *
     * @return array<array{d: string|null, fill?: string, opacity?: string, fill-opacity?: string, class?: string}>
     */
    protected function extractPaths(Crawler $svg): array
    {
        $paths = [];

        $svg->filter('path')->each(function (Crawler $path) use (&$paths): void {
            $pathData = [];

            // Only extract explicitly allowed attributes
            foreach (self::ALLOWED_PATH_ATTRIBUTES as $attr) {
                $value = $path->attr($attr);
                if ($value !== null && $value !== '') {
                    // Additional validation for class names
                    if ($attr === 'class' && ! $this->isValidClassName($value)) {
                        continue;
                    }
                    $pathData[$attr] = $value;
                }
            }

            // Only add path if it has a 'd' attribute
            if (isset($pathData['d'])) {
                $paths[] = $pathData;
            }
        });

        return $paths;
    }

    /**
     * Validate that a class name contains only safe characters.
     */
    protected function isValidClassName(string $class): bool
    {
        // Only allow alphanumeric, hyphens, underscores, and spaces
        return preg_match('/^[a-zA-Z0-9_\- ]+$/', $class) === 1;
    }

    /**
     * Extract important attributes from the SVG element.
     *
     * @return array<string, string>
     */
    protected function extractAttributes(Crawler $svg): array
    {
        $attributes = [];
        $node = $svg->getNode(0);

        // Type guard: ensure we have a DOMElement
        if (! $node instanceof \DOMElement) {
            return $attributes;
        }

        // Preserve xmlns if present
        if ($xmlns = $node->getAttribute('xmlns')) {
            $attributes['xmlns'] = $xmlns;
        }

        // Preserve aria-hidden if present
        if ($ariaHidden = $node->getAttribute('aria-hidden')) {
            $attributes['aria-hidden'] = $ariaHidden;
        }

        // Check for fill attribute
        if ($fill = $node->getAttribute('fill')) {
            $attributes['fill'] = $fill;
        }

        return $attributes;
    }

    /**
     * Generate Blade-compatible path markup from parsed data.
     *
     * @param array{viewBox: string, paths: array<array{d: string|null, fill?: string, opacity?: string, fill-opacity?: string, class?: string}>, attributes: array<string, string>} $parsedData
     */
    public function generatePathMarkup(array $parsedData): string
    {
        $paths = [];

        foreach ($parsedData['paths'] as $pathData) {
            $attributes = ['d' => $pathData['d']];

            // Add optional attributes
            if (isset($pathData['fill'])) {
                $attributes['fill'] = $pathData['fill'];
            }

            if (isset($pathData['opacity'])) {
                $attributes['opacity'] = $pathData['opacity'];
            }

            if (isset($pathData['fill-opacity'])) {
                $attributes['fill-opacity'] = $pathData['fill-opacity'];
            }

            if (isset($pathData['class'])) {
                $attributes['class'] = $pathData['class'];
            }

            $paths[] = $this->buildPathElement($attributes);
        }

        return implode("\n        ", $paths);
    }

    /**
     * Build a path element with attributes.
     *
     * @param array<string, string|null> $attributes
     */
    protected function buildPathElement(array $attributes): string
    {
        $attributeString = collect($attributes)
            ->map(fn ($value, string $key): string => sprintf('%s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES)))
            ->implode(' ');

        return sprintf('<path %s />', $attributeString);
    }

    /**
     * Check if SVG contains duotone paths (typically 2 paths with opacity).
     *
     * @param array{viewBox: string, paths: array<array{d: string|null, fill?: string, opacity?: string, fill-opacity?: string, class?: string}>, attributes: array<string, string>} $parsedData
     */
    public function isDuotone(array $parsedData): bool
    {
        $paths = $parsedData['paths'];

        if (count($paths) !== 2) {
            return false;
        }

        // Duotone icons typically have opacity or fill-opacity on one or both paths
        return isset($paths[0]['opacity']) || isset($paths[0]['fill-opacity']) ||
               isset($paths[1]['opacity']) || isset($paths[1]['fill-opacity']);
    }
}
