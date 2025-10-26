# Flux Font Awesome

A refined Laravel package that integrates Font Awesome icons with the Flux UI component library.

## Features

- Single-file, multi-variant icon components
- Full support for Font Awesome Free and Pro
- Seamless Flux UI integration with `data-flux-icon` attribute
- Flexible variant system (solid, regular, light, thin, duotone, brands, sharp variants)
- Artisan commands for easy setup and management
- Configurable fallback strategies
- Built-in size presets with Tailwind CSS
- Full test coverage

## Requirements

- PHP 8.2+
- Laravel 11.0+
- Font Awesome (Free or Pro)

## Installation

Install the package via Composer:

```bash
composer require tunr/flux-font-awesome
```

Run the installation command:

```bash
php artisan fa:install
```

This will:
- Publish the configuration file to `config/flux-font-awesome.php`
- Create the icon output directory at `resources/views/flux/icon/fa`
- Check for Font Awesome installation

## Font Awesome Setup

The package **automatically detects** Font Awesome installations in common locations:
- `node_modules/@fortawesome/fontawesome-free`
- `node_modules/@fortawesome/fontawesome-pro`
- `vendor/fortawesome/font-awesome`

### Standard Installation (Auto-detected)

```bash
# Font Awesome Free
npm install @fortawesome/fontawesome-free

# OR Font Awesome Pro
npm install @fortawesome/fontawesome-pro
```

That's it! The package will automatically find Font Awesome.

### Custom Path (Optional)

Only needed for non-standard installations. Set in your `.env`:

```env
FA_SOURCE_PATH=/custom/path/to/fontawesome
```

## Building Icons

Once Font Awesome is installed, build your icon components:

```bash
# Build all available icons
php artisan fa:build

# Build specific icons
php artisan fa:build user home github

# Build specific variants only
php artisan fa:build --variant=solid --variant=regular

# Rebuild all icons from scratch
php artisan fa:build --refresh

# Clean and rebuild
php artisan fa:build --clean
```

## Usage

### Basic Usage

Use Font Awesome icons in your Blade templates with the `flux:icon` component:

```blade
<flux:icon.fa.user />
<flux:icon.fa.home variant="regular" />
<flux:icon.fa.github variant="brands" />
```

### Variants

Specify different Font Awesome variants:

```blade
{{-- Free variants --}}
<flux:icon.fa.user variant="solid" />
<flux:icon.fa.user variant="regular" />
<flux:icon.fa.github variant="brands" />

{{-- Pro variants --}}
<flux:icon.fa.user variant="light" />
<flux:icon.fa.user variant="thin" />
<flux:icon.fa.user variant="duotone" />
<flux:icon.fa.user variant="sharp-solid" />
```

### Sizing

Use Tailwind CSS classes or the pre-configured size presets:

```blade
{{-- Using Tailwind classes directly --}}
<flux:icon.fa.user class="size-6" />
<flux:icon.fa.user class="size-8 text-blue-500" />

{{-- Using size presets from config --}}
{{-- xs: size-3, sm: size-4, base: size-5, md: size-6, lg: size-8, xl: size-10, 2xl: size-12 --}}
```

### Styling

Icons inherit `currentColor` by default, so they adapt to text color:

```blade
<div class="text-blue-500">
    <flux:icon.fa.user /> {{-- Will be blue --}}
</div>

<flux:icon.fa.user class="text-red-500" /> {{-- Explicitly red --}}
```

### Additional Attributes

Pass any HTML attributes:

```blade
<flux:icon.fa.user
    class="size-6 text-blue-500"
    aria-label="User icon"
    data-tooltip="User profile"
/>
```

## Configuration

The configuration file at `config/flux-font-awesome.php` allows you to customize:

### Paths

```php
'source_path' => env('FA_SOURCE_PATH', base_path('node_modules/@fortawesome/fontawesome-free')),
'output_path' => resource_path('views/flux/icon/fa'),
```

### Defaults

```php
'default_variant' => 'solid',
'fallback_strategy' => 'fallback', // or 'error'
'default_size' => 'md',
```

### Size Mappings

```php
'sizes' => [
    'xs' => 'size-3',   // 12px
    'sm' => 'size-4',   // 16px
    'base' => 'size-5', // 20px
    'md' => 'size-6',   // 24px
    'lg' => 'size-8',   // 32px
    'xl' => 'size-10',  // 40px
    '2xl' => 'size-12', // 48px
],
```

### Build Settings

```php
'build' => [
    'include' => [], // Specific icons to build (empty = all)
    'exclude' => [], // Icons to exclude
    'variants' => [], // Variants to build (empty = all)
    'preserve_existing' => false,
],
```

### Duotone Settings

```php
'duotone' => [
    'primary_opacity' => 1.0,
    'secondary_opacity' => 0.4,
],
```

## Artisan Commands

### `fa:install`

Install and set up the package.

```bash
php artisan fa:install
php artisan fa:install --force  # Overwrite existing config
```

### `fa:build`

Build icon components from Font Awesome SVGs.

```bash
php artisan fa:build                              # Build all icons
php artisan fa:build user home github             # Build specific icons
php artisan fa:build --variant=solid              # Build solid variant only
php artisan fa:build --variant=solid --variant=regular  # Multiple variants
php artisan fa:build --refresh                    # Rebuild all icons
php artisan fa:build --clean                      # Remove existing before building
```

### `fa:config`

Display current configuration.

```bash
php artisan fa:config
```

### `fa:status`

Show package status and statistics.

```bash
php artisan fa:status
```

## How It Works

### Single-file, Multi-variant Architecture

Each icon is stored as a single Blade component file containing all available variants:

```blade
{{-- resources/views/flux/icon/fa/user.blade.php --}}
@props(['variant' => 'solid'])

@php
$viewBox = match($resolvedVariant) {
    'solid' => '0 0 448 512',
    'regular' => '0 0 448 512',
    default => '0 0 448 512',
};
@endphp

<svg {{ $attributes->merge(['class' => 'shrink-0']) }}
     viewBox="{{ $viewBox }}"
     data-flux-icon
     aria-hidden="true"
     fill="currentColor">
    @switch($resolvedVariant)
        @case('solid')
            <path d="..." />
            @break
        @case('regular')
            <path d="..." />
            @break
        @default
            <path d="..." />
    @endswitch
</svg>
```

### Flux UI Integration

Icons include the `data-flux-icon` attribute and follow Flux's recommended structure:
- `shrink-0` base class
- `fill="currentColor"` for color inheritance
- `aria-hidden="true"` for accessibility
- Proper attribute forwarding via `{{ $attributes }}`

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

Check code style:

```bash
composer pint
```

## Development Workflow

1. **Clone the repository**

```bash
git clone https://github.com/tunr/flux-font-awesome.git
cd flux-font-awesome
```

2. **Install dependencies**

```bash
composer install
```

3. **Run tests**

```bash
composer test
```

4. **Create a new feature**

- Write tests first
- Implement the feature
- Ensure all tests pass
- Check code style with Pint

## Troubleshooting

### Icons not appearing

1. Check Font Awesome is installed:
```bash
php artisan fa:status
```

2. Verify source path in config or `.env`:
```env
FA_SOURCE_PATH=node_modules/@fortawesome/fontawesome-free
```

3. Rebuild icons:
```bash
php artisan fa:build --refresh
```

### Missing variants

Some icons may not be available in all variants. The package will:
- Fall back to the default variant (configurable)
- Log a warning in debug mode
- Continue rendering gracefully

Check available variants:
```bash
php artisan fa:status
```

### Performance considerations

- Icons are static Blade components (no runtime overhead)
- Build icons during deployment, not at runtime
- Only build the icons you need using the `include` config option

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## License

This package is open-source software licensed under the [MIT license](LICENSE.md).

## Credits

- **Author**: [tunr](https://github.com/tunr)
- **Font Awesome**: [fontawesome.com](https://fontawesome.com)
- **Flux UI**: [fluxui.dev](https://fluxui.dev)
- **Laravel**: [laravel.com](https://laravel.com)

## Support

- **Issues**: [GitHub Issues](https://github.com/tunr/flux-font-awesome/issues)
- **Discussions**: [GitHub Discussions](https://github.com/tunr/flux-font-awesome/discussions)
