<?php

namespace Tunr\FluxFontAwesome\Support;

class Variant
{
    /**
     * Font Awesome Free variants.
     */
    public const FREE_SOLID = 'solid';

    public const FREE_REGULAR = 'regular';

    public const FREE_BRANDS = 'brands';

    /**
     * Font Awesome Pro variants (additional).
     */
    public const PRO_LIGHT = 'light';

    public const PRO_THIN = 'thin';

    public const PRO_DUOTONE = 'duotone';

    public const PRO_SHARP_SOLID = 'sharp-solid';

    public const PRO_SHARP_REGULAR = 'sharp-regular';

    public const PRO_SHARP_LIGHT = 'sharp-light';

    public const PRO_SHARP_THIN = 'sharp-thin';

    /**
     * All available Font Awesome variants.
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
     * Free-only variants.
     */
    public const FREE = [
        self::FREE_SOLID,
        self::FREE_REGULAR,
        self::FREE_BRANDS,
    ];

    /**
     * Pro-only variants.
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
     * Map variant names to their directory names in Font Awesome packages.
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
     * Check if a variant is valid.
     */
    public static function isValid(string $variant): bool
    {
        return in_array($variant, self::ALL, true);
    }

    /**
     * Check if a variant is a Free variant.
     */
    public static function isFree(string $variant): bool
    {
        return in_array($variant, self::FREE, true);
    }

    /**
     * Check if a variant is a Pro variant.
     */
    public static function isPro(string $variant): bool
    {
        return in_array($variant, self::PRO, true);
    }

    /**
     * Get the directory name for a variant.
     */
    public static function toDirectory(string $variant): string
    {
        return self::DIRECTORY_MAP[$variant] ?? $variant;
    }

    /**
     * Get all variants or filter by type.
     *
     * @return array<int, string>
     */
    public static function all(?string $type = null): array
    {
        return match ($type) {
            'free' => self::FREE,
            'pro' => self::PRO,
            default => self::ALL,
        };
    }
}
