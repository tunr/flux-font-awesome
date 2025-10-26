# Development Guide

This guide is for contributors and maintainers of the Flux Font Awesome package.

## Setup

### Clone and Install

```bash
git clone https://github.com/tunr/flux-font-awesome.git
cd flux-font-awesome
composer install
```

### Install Font Awesome for Testing

```bash
npm install @fortawesome/fontawesome-free
```

## Development Workflow

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/VariantTest.php

# Run with coverage
composer test:coverage
# Coverage report will be in coverage/index.html
```

### Code Style

```bash
# Check code style
composer pint:test

# Fix code style
composer pint
```

### Before Committing

Always run:
```bash
composer pint
composer test
```

## Architecture Overview

### Single-file, Multi-variant Pattern

Each icon is a single Blade component containing all variants:

```blade
@props(['variant' => 'solid'])

<svg>
    @switch($variant)
        @case('solid')
            <path d="..." />
            @break
        @case('regular')
            <path d="..." />
            @break
    @endswitch
</svg>
```

### Service Layer

**SvgParser**: Extracts paths and viewBox from Font Awesome SVG files
- Handles single and multi-path icons
- Preserves duotone opacity attributes
- Generates Blade-ready markup

**IconWriter**: Creates Blade component files
- Combines multiple variants into single file
- Generates switch statements for variant selection
- Manages file I/O

**Variant**: Constants and helpers for Font Awesome variants
- Validates variant names
- Maps to directory names
- Handles aliases (fas, far, etc.)

### Command Layer

All commands extend `Illuminate\Console\Command`:

- **InstallCommand**: Initial setup
- **BuildCommand**: Icon generation (most complex)
- **ConfigCommand**: Display configuration
- **StatusCommand**: Package health check

## Testing Strategy

### Unit Tests

Test individual classes in isolation:
- `VariantTest`: Variant validation, normalization
- `SvgParserTest`: SVG parsing, path extraction

### Feature Tests

Test commands and integration:
- `InstallCommandTest`: Installation flow
- `ConfigCommandTest`: Configuration display

### Test Fixtures

Place test SVG files in `tests/fixtures/fontawesome/svgs/`:
```
tests/fixtures/
└── fontawesome/
    └── svgs/
        ├── solid/
        │   └── user.svg
        └── regular/
            └── user.svg
```

## Adding New Features

### 1. Write Tests First (TDD)

```php
// tests/Unit/NewFeatureTest.php
public function it_does_something_new(): void
{
    // Arrange
    $service = new MyService();

    // Act
    $result = $service->doSomething();

    // Assert
    $this->assertTrue($result);
}
```

### 2. Implement the Feature

```php
// src/Services/MyService.php
public function doSomething(): bool
{
    // Implementation
    return true;
}
```

### 3. Update Documentation

- Add to README.md if user-facing
- Update CHANGELOG.md
- Add code comments

### 4. Run Full Test Suite

```bash
composer pint
composer test
```

## Release Process

### 1. Update Version

Update CHANGELOG.md:
```markdown
## [1.1.0] - 2025-01-15

### Added
- New feature description
```

### 2. Tag Release

```bash
git tag -a v1.1.0 -m "Release version 1.1.0"
git push origin v1.1.0
```

### 3. GitHub Release

Create a release on GitHub with:
- Release notes from CHANGELOG
- Migration guide if needed
- Breaking changes highlighted

## Common Tasks

### Adding a New Command

1. Create command class in `src/Commands/`
2. Register in `FluxFontAwesomeServiceProvider`
3. Write feature test
4. Update README with command usage

### Updating Config Schema

1. Update `config/flux-font-awesome.php`
2. Update README configuration section
3. Add migration notes if breaking
4. Test with `fa:config` command

### Handling Font Awesome Updates

When Font Awesome releases new variants:
1. Update `Variant` class constants
2. Update `DIRECTORY_MAP`
3. Add tests for new variants
4. Update README

## Debugging

### Testing Commands Manually

Use Orchestra Testbench to simulate Laravel:

```php
public function test_command(): void
{
    $this->artisan('fa:build')
        ->expectsOutput('Building...')
        ->assertSuccessful();
}
```

### Verbose Output

Run commands with `-v` flag:
```bash
php artisan fa:build -v
```

### Inspecting Generated Icons

Check output at:
```
resources/views/flux/icon/fa/
```

## CI/CD

GitHub Actions runs on:
- Push to main/develop
- Pull requests

Workflow tests:
- PHP 8.2 and 8.3
- Laravel 11
- Both prefer-lowest and prefer-stable

## Performance Considerations

### Icon Building

- `fa:build` scans all SVG files
- For large icon sets (1000+), use progress bar
- Consider adding `--parallel` in future

### Runtime Performance

- Icons are static Blade components (zero runtime overhead)
- No database queries
- No API calls
- Pure SVG rendering

## Future Enhancements

Ideas for future versions:
- [ ] Icon search/discovery command (`fa:list`)
- [ ] IDE autocomplete helper generator
- [ ] Parallel icon building
- [ ] Icon preview in terminal
- [ ] Migration between Free/Pro
- [ ] Custom icon additions
- [ ] Icon caching/optimization

## Questions?

- Open a discussion
- Check existing issues
- Review the design spec

Happy coding!
