<?php

namespace Tunr\FluxFontAwesome\Services;

use Symfony\Component\DomCrawler\Crawler;

class SvgParser
{
    /**
     * Parse an SVG file and extract its contents.
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
        $viewBox = $svg->attr('viewBox');

        if (empty($viewBox)) {
            // Fallback to common Font Awesome viewBox
            return '0 0 512 512';
        }

        return $viewBox;
    }

    /**
     * Extract all path elements from an SVG.
     */
    protected function extractPaths(Crawler $svg): array
    {
        $paths = [];

        $svg->filter('path')->each(function (Crawler $path) use (&$paths) {
            $pathData = [
                'd' => $path->attr('d'),
            ];

            // Preserve attributes like fill, opacity, class for duotone support
            if ($fill = $path->attr('fill')) {
                $pathData['fill'] = $fill;
            }

            if ($opacity = $path->attr('opacity')) {
                $pathData['opacity'] = $opacity;
            }

            if ($fillOpacity = $path->attr('fill-opacity')) {
                $pathData['fill-opacity'] = $fillOpacity;
            }

            if ($class = $path->attr('class')) {
                $pathData['class'] = $class;
            }

            $paths[] = $pathData;
        });

        return $paths;
    }

    /**
     * Extract important attributes from the SVG element.
     */
    protected function extractAttributes(Crawler $svg): array
    {
        $attributes = [];

        // Preserve xmlns if present
        if ($xmlns = $svg->attr('xmlns')) {
            $attributes['xmlns'] = $xmlns;
        }

        // Preserve aria-hidden if present
        if ($ariaHidden = $svg->attr('aria-hidden')) {
            $attributes['aria-hidden'] = $ariaHidden;
        }

        // Check for fill attribute
        if ($fill = $svg->attr('fill')) {
            $attributes['fill'] = $fill;
        }

        return $attributes;
    }

    /**
     * Generate Blade-compatible path markup from parsed data.
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
     */
    protected function buildPathElement(array $attributes): string
    {
        $attributeString = collect($attributes)
            ->map(fn ($value, $key) => sprintf('%s="%s"', $key, htmlspecialchars($value, ENT_QUOTES)))
            ->implode(' ');

        return sprintf('<path %s />', $attributeString);
    }

    /**
     * Check if SVG contains duotone paths (typically 2 paths with opacity).
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
