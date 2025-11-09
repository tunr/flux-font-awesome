<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Font Awesome Source Path (Optional)
    |--------------------------------------------------------------------------
    |
    | The package automatically detects Font Awesome installations in common
    | locations (node_modules, vendor). Only set this if you have a custom
    | installation path. Leave as null for automatic detection.
    |
    | Common paths checked automatically:
    | - node_modules/@fortawesome/fontawesome-free
    | - node_modules/@fortawesome/fontawesome-pro
    | - vendor/fortawesome/font-awesome
    |
    */

    'source_path' => env('FA_SOURCE_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Default Variant
    |--------------------------------------------------------------------------
    |
    | The default Font Awesome variant to use when none is specified.
    | Available: 'solid', 'regular', 'light', 'thin', 'duotone', 'brands'
    |
    */

    'default_variant' => 'solid',

    /*
    |--------------------------------------------------------------------------
    | Size Mappings
    |--------------------------------------------------------------------------
    |
    | Tailwind CSS size classes for different icon sizes. These map to the
    | 'size' prop on the icon component (e.g., <flux:icon.fa.user size="lg" />).
    |
    */

    'sizes' => [
        'xs' => 'size-4',      // 16px (matches Flux micro)
        'sm' => 'size-5',      // 20px (matches Flux mini)
        'md' => 'size-6',      // 24px (matches Flux outline/solid, default)
        'lg' => 'size-8',      // 32px
        'xl' => 'size-10',     // 40px
        '2xl' => 'size-12',    // 48px
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Size
    |--------------------------------------------------------------------------
    |
    | The default size to use when none is specified.
    |
    */

    'default_size' => 'md',

    /*
    |--------------------------------------------------------------------------
    | Icon Scale
    |--------------------------------------------------------------------------
    |
    | Visual scaling factor to add optical padding around FA icons for better
    | alignment with Heroicons and text. FA icons fill their viewBox completely
    | while Heroicons have internal padding. This scale compensates visually.
    |
    | Set to 1.0 to disable scaling.
    | Recommended range: 0.85 - 0.95 (5-15% breathing room)
    | Start at 0.90 and adjust to taste.
    |
    */

    'icon_scale' => 0.875,

    /*
    |--------------------------------------------------------------------------
    | Build Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the flux-fa:build command.
    |
    | Include specific icons to avoid building all 2000+ Font Awesome icons.
    | If empty, you must specify icons in the command (e.g., flux-fa:build user home).
    |
    */

    'build' => [
        // Specific icons to include (leave empty to require explicit icon arguments)
        'include' => [],

        // Icons to exclude from build
        'exclude' => [],
    ],

];
