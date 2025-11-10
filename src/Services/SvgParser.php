<?php

namespace Tunr\FluxFontAwesome\Services;

use Symfony\Component\DomCrawler\Crawler;

class SvgParser
{
    /**
     * Parse an SVG file and extract its contents.
     *
     * @return array{viewBox: string, paths: array<int, array<string, string>>, attributes: array<string, string>}
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

        if (! $viewBox) {
            // Fallback to common Font Awesome viewBox
            return '0 0 512 512';
        }

        return $viewBox;
    }

    /**
     * Extract all path elements from an SVG.
     *
     * @return array<int, array<string, string>>
     */
    protected function extractPaths(Crawler $svg): array
    {
        $paths = [];

        $svg->filter('path')->each(function (Crawler $path) use (&$paths): void {
            $d = $path->attr('d');
            if ($d === null) {
                return;
            }

            $pathData = ['d' => $d];

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
     * @param array{viewBox: string, paths: array<int, array<string, string>>, attributes: array<string, string>} $parsedData
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
     * @param array<string, string> $attributes
     */
    protected function buildPathElement(array $attributes): string
    {
        $attributeString = collect($attributes)
            ->map(fn ($value, string $key): string => sprintf('%s="%s"', $key, htmlspecialchars((string) $value, ENT_QUOTES)))
            ->implode(' ');

        return sprintf('<path %s />', $attributeString);
    }
}
