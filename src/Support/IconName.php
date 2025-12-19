<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome\Support;

class IconName
{
    /**
     * Pattern for valid icon names.
     * Allows lowercase letters, numbers, and hyphens (Font Awesome naming convention).
     */
    private const VALID_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    /**
     * Validate an icon name for security and format.
     *
     * @throws \InvalidArgumentException If the icon name is invalid
     */
    public static function validate(string $name): string
    {
        $name = strtolower(trim($name));

        if ($name === '') {
            throw new \InvalidArgumentException('Icon name cannot be empty');
        }

        // Reject path traversal attempts
        if (str_contains($name, '..') || str_contains($name, '/') || str_contains($name, '\\')) {
            throw new \InvalidArgumentException(
                "Invalid icon name '{$name}': path traversal characters are not allowed"
            );
        }

        // Reject hidden files
        if (str_starts_with($name, '.')) {
            throw new \InvalidArgumentException(
                "Invalid icon name '{$name}': cannot start with a dot"
            );
        }

        // Validate format matches Font Awesome naming convention
        if (! preg_match(self::VALID_PATTERN, $name)) {
            throw new \InvalidArgumentException(
                "Invalid icon name '{$name}': only lowercase letters, numbers, and hyphens are allowed"
            );
        }

        return $name;
    }

    /**
     * Check if an icon name is valid without throwing.
     */
    public static function isValid(string $name): bool
    {
        try {
            self::validate($name);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
