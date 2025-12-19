<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Support;

class Style
{
    /**
     * Font Awesome Free styles.
     */
    public const FREE_SOLID = 'solid';

    public const FREE_REGULAR = 'regular';

    public const FREE_BRANDS = 'brands';

    /**
     * Font Awesome Pro styles (additional).
     */
    public const PRO_LIGHT = 'light';

    public const PRO_THIN = 'thin';

    public const PRO_DUOTONE = 'duotone';

    public const PRO_SHARP_SOLID = 'sharp-solid';

    public const PRO_SHARP_REGULAR = 'sharp-regular';

    public const PRO_SHARP_LIGHT = 'sharp-light';

    public const PRO_SHARP_THIN = 'sharp-thin';

    /**
     * All available Font Awesome styles.
     */
    public const ALL = [
        self::FREE_SOLID,
        self::FREE_REGULAR,
        self::FREE_BRANDS,
        self::PRO_LIGHT,
        self::PRO_THIN,
        self::PRO_DUOTONE,
        self::PRO_SHARP_SOLID,
        self::PRO_SHARP_REGULAR,
        self::PRO_SHARP_LIGHT,
        self::PRO_SHARP_THIN,
    ];

    /**
     * Free-only styles.
     */
    public const FREE = [
        self::FREE_SOLID,
        self::FREE_REGULAR,
        self::FREE_BRANDS,
    ];

    /**
     * Pro-only styles.
     */
    public const PRO = [
        self::PRO_LIGHT,
        self::PRO_THIN,
        self::PRO_DUOTONE,
        self::PRO_SHARP_SOLID,
        self::PRO_SHARP_REGULAR,
        self::PRO_SHARP_LIGHT,
        self::PRO_SHARP_THIN,
    ];

    /**
     * Map style names to their directory names in Font Awesome packages.
     */
    public const DIRECTORY_MAP = [
        self::FREE_SOLID => 'solid',
        self::FREE_REGULAR => 'regular',
        self::FREE_BRANDS => 'brands',
        self::PRO_LIGHT => 'light',
        self::PRO_THIN => 'thin',
        self::PRO_DUOTONE => 'duotone',
        self::PRO_SHARP_SOLID => 'sharp-solid',
        self::PRO_SHARP_REGULAR => 'sharp-regular',
        self::PRO_SHARP_LIGHT => 'sharp-light',
        self::PRO_SHARP_THIN => 'sharp-thin',
    ];

    /**
     * Check if a style is valid.
     */
    public static function isValid(string $style): bool
    {
        return in_array($style, self::ALL, true);
    }

    /**
     * Check if a style is a Free style.
     */
    public static function isFree(string $style): bool
    {
        return in_array($style, self::FREE, true);
    }

    /**
     * Check if a style is a Pro style.
     */
    public static function isPro(string $style): bool
    {
        return in_array($style, self::PRO, true);
    }

    /**
     * Get the directory name for a style.
     */
    public static function toDirectory(string $style): string
    {
        return self::DIRECTORY_MAP[$style] ?? $style;
    }

    /**
     * Get all styles or filter by type.
     *
     * @return array<string>
     */
    public static function all(?string $type = null): array
    {
        return match ($type) {
            'free' => self::FREE,
            'pro' => self::PRO,
            default => self::ALL,
        };
    }

    /**
     * Normalize a style name (handle aliases).
     *
     * @throws \InvalidArgumentException If the style is not valid
     */
    public static function normalize(string $style): string
    {
        $normalized = match (strtolower($style)) {
            'fas', 's' => self::FREE_SOLID,
            'far', 'r' => self::FREE_REGULAR,
            'fab', 'b' => self::FREE_BRANDS,
            'fal', 'l' => self::PRO_LIGHT,
            'fat', 't' => self::PRO_THIN,
            'fad', 'd' => self::PRO_DUOTONE,
            'fass', 'ss' => self::PRO_SHARP_SOLID,
            'fasr', 'sr' => self::PRO_SHARP_REGULAR,
            'fasl', 'sl' => self::PRO_SHARP_LIGHT,
            'fast', 'st' => self::PRO_SHARP_THIN,
            default => strtolower($style),
        };

        if (! self::isValid($normalized)) {
            throw new \InvalidArgumentException(
                "Invalid style '{$style}'. Valid styles: ".implode(', ', self::ALL)
            );
        }

        return $normalized;
    }
}
