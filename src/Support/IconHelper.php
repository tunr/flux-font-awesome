<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Support;

class IconHelper
{
    /**
     * Resolve the style (FA weight) to use, falling back to config default.
     */
    public static function resolveStyle(?string $style): string
    {
        return $style ?? config('flux-font-awesome.default_variant', 'solid');
    }

    /**
     * Resolve the size to use, falling back to config default.
     */
    public static function resolveSize(?string $size): string
    {
        return $size ?? config('flux-font-awesome.default_size', 'md');
    }

    /**
     * Resolve the size class based on Flux variant or FA size prop.
     * Flux variant takes precedence for compatibility with Flux UI components.
     */
    public static function resolveSizeClass(?string $variant, ?string $size): string
    {
        // Map Flux variants to sizes (Flux components pass variant for sizing)
        $fluxVariantToSize = [
            'outline' => '[:where(&)]:size-6',  // 24px
            'solid' => '[:where(&)]:size-6',    // 24px
            'mini' => '[:where(&)]:size-5',     // 20px
            'micro' => '[:where(&)]:size-4',    // 16px
        ];

        // Flux variant takes precedence
        if ($variant && isset($fluxVariantToSize[$variant])) {
            return $fluxVariantToSize[$variant];
        }

        // Fall back to FA size with low specificity
        $resolvedSize = static::resolveSize($size);
        $sizes = config('flux-font-awesome.sizes', []);
        $configSize = $sizes[$resolvedSize] ?? $sizes['md'] ?? 'size-6';

        return '[:where(&)]:'.$configSize;
    }

    /**
     * Get the inline scale style if icon scaling is configured.
     * Returns empty string if scale is 1.0 (no scaling).
     */
    public static function getScaleStyle(): string
    {
        $scale = config('flux-font-awesome.icon_scale', 1.0);

        if ($scale === 1.0) {
            return '';
        }

        return ' style="transform: scale('.$scale.'); transform-origin: center center;"';
    }
}
