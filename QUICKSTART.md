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

Choose one:

**Option A: Font Awesome Free (via NPM)**
```bash
npm install @fortawesome/fontawesome-free
```

**Option B: Font Awesome Pro (via NPM)**
```bash
npm install @fortawesome/fontawesome-pro
```

Then add to your `.env`:
```env
FA_SOURCE_PATH=node_modules/@fortawesome/fontawesome-pro
```

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
<flux:icon.fa.user />
<flux:icon.fa.home />
<flux:icon.fa.github />
```

### With Variants

```blade
{{-- Solid (default) --}}
<flux:icon.fa.user variant="solid" />

{{-- Regular --}}
<flux:icon.fa.user variant="regular" />

{{-- Brands --}}
<flux:icon.fa.github variant="brands" />

{{-- Pro variants (if you have Pro) --}}
<flux:icon.fa.user variant="light" />
<flux:icon.fa.user variant="thin" />
<flux:icon.fa.user variant="duotone" />
```

### With Styling

```blade
{{-- Size and color --}}
<flux:icon.fa.user class="size-6 text-blue-500" />

{{-- Larger --}}
<flux:icon.fa.user class="size-8" />

{{-- Inherits text color --}}
<div class="text-red-500">
    <flux:icon.fa.user />
</div>
```

### With Additional Attributes

```blade
<flux:icon.fa.user
    variant="regular"
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

Add to `.env`:
```env
FA_SOURCE_PATH=/path/to/your/fontawesome
```

Or edit `config/flux-font-awesome.php`:
```php
'source_path' => base_path('path/to/fontawesome'),
```

## Need Help?

- [Full Documentation](README.md)
- [GitHub Issues](https://github.com/tunr/flux-font-awesome/issues)
- [Discussions](https://github.com/tunr/flux-font-awesome/discussions)
