# Flux Font Awesome

[![Tests](https://github.com/tunr/flux-font-awesome/actions/workflows/tests.yml/badge.svg)](https://github.com/tunr/flux-font-awesome/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE.md)

A Laravel package that integrates Font Awesome icons with the Flux UI component library.

## Features

- Single-file, multi-variant icon components
- Full support for Font Awesome Free and Pro
- Seamless Flux UI integration
- Artisan commands for building and managing icons

## Requirements

- PHP 8.2+
- Laravel 11.0+
- Font Awesome (Free or Pro)

## Installation

```bash
composer require tunr/flux-font-awesome
```

```bash
php artisan flux-fa:install
```

## Font Awesome Setup

The package automatically detects Font Awesome in common locations:
- `node_modules/@fortawesome/fontawesome-free`
- `node_modules/@fortawesome/fontawesome-pro`
- `vendor/fortawesome/font-awesome`

### Font Awesome Free (npm)

```bash
npm install @fortawesome/fontawesome-free
```

### Font Awesome Pro (npm)

```bash
# Configure registry
npm config set "@fortawesome:registry" https://npm.fontawesome.com/
npm config set "//npm.fontawesome.com/:_authToken" YOUR_TOKEN_HERE

# Install
npm install @fortawesome/fontawesome-pro
```

### Font Awesome Pro (Composer)

Add to `composer.json`:

```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://composer.fontawesome.com"
        }
    ]
}
```

Add `auth.json` with your token:

```json
{
    "http-basic": {
        "composer.fontawesome.com": {
            "username": "YOUR_TOKEN_HERE",
            "password": "retrieve"
        }
    }
}
```

Then install:

```bash
composer require fortawesome/font-awesome
```

### Custom Path

For non-standard installations, set in `.env`:

```env
FA_SOURCE_PATH=/custom/path/to/fontawesome
```

## Building Icons

Build the icons you need:

```bash
# Build specific icons
php artisan flux-fa:build user home github

# Build with specific variants
php artisan flux-fa:build user --variant=solid --variant=regular
```

## Usage

### Basic Usage

```blade
{{-- Component syntax --}}
<flux:icon.fa-user />
<flux:icon.fa-github style="brands" />

{{-- Name attribute syntax --}}
<flux:icon name="fa-user" />

{{-- In Flux components --}}
<flux:button icon="fa-user">Profile</flux:button>
```

### Variants

```blade
{{-- Free variants --}}
<flux:icon.fa-user style="solid" />
<flux:icon.fa-user style="regular" />
<flux:icon.fa-github style="brands" />

{{-- Pro variants --}}
<flux:icon.fa-user style="light" />
<flux:icon.fa-user style="thin" />
<flux:icon.fa-user style="duotone" />
```

### Sizing

```blade
{{-- Tailwind classes --}}
<flux:icon.fa-user class="size-6" />

{{-- Size presets --}}
<flux:icon.fa-user size="sm" />  {{-- size-5 --}}
<flux:icon.fa-user size="md" />  {{-- size-6 (default) --}}
<flux:icon.fa-user size="lg" />  {{-- size-8 --}}
```

### Styling

Icons inherit `currentColor`:

```blade
<div class="text-blue-500">
    <flux:icon.fa-user />
</div>

<flux:icon.fa-user class="text-red-500" />
```

## Commands

| Command | Description |
|---------|-------------|
| `flux-fa:install` | Set up the package |
| `flux-fa:build <icons...>` | Build icon components |
| `flux-fa:list` | List built icons |
| `flux-fa:status` | Check installation status |

## Configuration

Publish the config file during installation. Key options:

```php
// config/flux-font-awesome.php

'source_path' => env('FA_SOURCE_PATH'),  // Auto-detected if null
'output_path' => resource_path('views/flux/icon'),
'default_variant' => 'solid',
'default_size' => 'md',

// Limit which variants are built (empty = all available)
'build' => [
    'variants' => [],
],
```

## Troubleshooting

**Icons not appearing?**

```bash
php artisan flux-fa:status
```

**Rebuild icons after upgrading Font Awesome:**

```bash
php artisan flux-fa:list              # See what you have
php artisan flux-fa:build user home   # Rebuild those icons
```

## Testing

```bash
composer test
composer pint
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

MIT License. See [LICENSE.md](LICENSE.md).

## Credits

- [Font Awesome](https://fontawesome.com)
- [Flux UI](https://fluxui.dev)
- [Laravel](https://laravel.com)
