# Quick Start Guide

Get up and running with Flux Font Awesome in 5 minutes.

## Prerequisites

- Laravel 11.0+
- PHP 8.2+
- Composer

## Installation

### Step 1: Install the Package

```bash
composer require tunr/flux-font-awesome
```

### Step 2: Install Font Awesome

**Font Awesome Free:**
```bash
npm install @fortawesome/fontawesome-free
```

**Font Awesome Pro (NPM):**
```bash
# 1. Configure NPM registry
npm config set "@fortawesome:registry" https://npm.fontawesome.com/
npm config set "//npm.fontawesome.com/:_authToken" YOUR_TOKEN_HERE

# 2. Install Pro
npm install @fortawesome/fontawesome-pro
```

**Font Awesome Pro (Composer):**

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

The package **automatically detects** your installation - no configuration needed!

### Step 3: Run Installation Command

```bash
php artisan fa:install
```

This will:
- Publish the config file
- Create the icon directory
- Verify Font Awesome installation

### Step 4: Build Icons

```bash
# Build all icons
php artisan fa:build

# OR build specific icons only
php artisan fa:build user home github star
```

## Usage

### Basic Icons

```blade
<flux:icon.fa-user />
<flux:icon.fa-home />
<flux:icon.fa-github />
```

### With Variants

```blade
{{-- Solid (default) --}}
<flux:icon.fa-user style="solid" />

{{-- Regular --}}
<flux:icon.fa-user style="regular" />

{{-- Brands --}}
<flux:icon.fa-github style="brands" />

{{-- Pro variants (if you have Pro) --}}
<flux:icon.fa-user style="light" />
<flux:icon.fa-user style="thin" />
<flux:icon.fa-user style="duotone" />
```

### With Styling

```blade
{{-- Size and color --}}
<flux:icon.fa-user class="size-6 text-blue-500" />

{{-- Larger --}}
<flux:icon.fa-user class="size-8" />

{{-- Inherits text color --}}
<div class="text-red-500">
    <flux:icon.fa-user />
</div>
```

### With Additional Attributes

```blade
<flux:icon.fa-user
    style="regular"
    class="size-6 text-gray-700"
    aria-label="User profile"
    data-tooltip="View profile"
/>
```

## Common Commands

```bash
# View configuration
php artisan fa:config

# Check package status
php artisan fa:status

# Rebuild all icons
php artisan fa:build --refresh

# Build specific variants only
php artisan fa:build --variant=solid --variant=regular
```

## Configuration

Edit `config/flux-font-awesome.php` to customize:

```php
return [
    // Change default variant
    'default_variant' => 'solid',

    // Change fallback behavior
    'fallback_strategy' => 'fallback', // or 'error'

    // Build only specific icons
    'build' => [
        'include' => ['user', 'home', 'github'],
        'exclude' => [],
        'variants' => ['solid', 'regular'],
    ],
];
```

## Next Steps

- Read the full [README.md](README.md)
- Check [available variants](README.md#variants)
- Explore [configuration options](README.md#configuration)
- Learn about [size presets](README.md#sizing)

## Troubleshooting

**Icons not showing?**
```bash
# Check status
php artisan fa:status

# Rebuild
php artisan fa:build --refresh
```

**Font Awesome not found?**

The package checks these locations automatically:
- `node_modules/@fortawesome/fontawesome-free`
- `node_modules/@fortawesome/fontawesome-pro`
- `vendor/fortawesome/font-awesome`

For custom paths, add to `.env`:
```env
FA_SOURCE_PATH=/path/to/your/fontawesome
```

## Need Help?

- [Full Documentation](README.md)
- [GitHub Issues](https://github.com/tunr/flux-font-awesome/issues)
- [Discussions](https://github.com/tunr/flux-font-awesome/discussions)
