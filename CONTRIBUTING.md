# Contributing to Flux Font Awesome

Thank you for considering contributing to Flux Font Awesome! This guide will help you get started.

## Code of Conduct

Be respectful, inclusive, and constructive in all interactions.

## Getting Started

### Development Setup

1. **Fork the repository** and clone it locally:
```bash
git clone https://github.com/YOUR-USERNAME/flux-font-awesome.git
cd flux-font-awesome
```

2. **Install dependencies**:
```bash
composer install
```

3. **Install Font Awesome for testing**:
```bash
npm install @fortawesome/fontawesome-free
```

4. **Create a new branch** for your feature or bug fix:
```bash
git checkout -b feature/your-feature-name
```

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

This project uses Laravel Pint for code formatting:

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

Understanding the package architecture will help you contribute effectively.

### Single-file, Multi-variant Pattern

Each icon is a single Blade component containing all variants:

```blade
@props(['style' => config('flux-font-awesome.default_variant', 'solid')])

<svg {{ $attributes }} ...>
    @switch($style)
        @case('solid')
            <path d="..." />
            @break
        @case('regular')
            <path d="..." />
            @break
    @endswitch
</svg>
```

This approach:
- Keeps variants together (easier maintenance)
- Single file per icon (simpler structure)
- Runtime variant selection (flexible usage)

### Service Layer

**SvgParser** (`src/Services/SvgParser.php`)
- Extracts paths and viewBox from Font Awesome SVG files
- Handles single and multi-path icons
- Preserves duotone opacity attributes
- Generates Blade-ready markup

**IconWriter** (`src/Services/IconWriter.php`)
- Creates Blade component files from parsed SVG data
- Combines multiple variants into single file
- Generates switch statements for variant selection
- Manages file I/O

**FontAwesomePathResolver** (`src/Services/FontAwesomePathResolver.php`)
- Auto-detects Font Awesome installations (NPM or Composer)
- Resolves paths to SVG source files
- Handles both Free and Pro editions

**Variant** (`src/Support/Variant.php`)
- Constants for all Font Awesome variants
- Validates variant names
- Maps to directory names
- Build command auto-detects all available variants

### Command Layer

All commands extend `Illuminate\Console\Command`:

- **InstallCommand**: Initial package setup
- **BuildCommand**: Icon generation (most complex, orchestrates parsing and writing)
- **ConfigCommand**: Display current configuration
- **StatusCommand**: Package health check

## Testing Strategy

### Unit Tests

Test individual classes in isolation:
- `VariantTest`: Variant validation, normalization, aliases
- `SvgParserTest`: SVG parsing, path extraction
- `IconHelperTest`: Size resolution, class generation
- `IconWriterTest`: File generation, template rendering

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

## Pull Request Process

### 1. Write Clear Commit Messages

Follow conventional commits format:
- `feat: add new feature`
- `fix: resolve bug`
- `docs: update documentation`
- `test: add missing tests`
- `refactor: improve code structure`

### 2. Update Documentation

- Update README.md for user-facing changes
- Update CHANGELOG.md with your changes
- Add inline comments for complex logic
- Keep examples up to date

### 3. Ensure Quality

- All tests must pass
- Code style must be clean (run `composer pint`)
- Maintain or improve code coverage
- Follow existing patterns and conventions

### 4. Write a Clear PR Description

Explain:
- What changes you made
- Why you made them
- How to test them
- Any breaking changes

### 5. Be Responsive

- Respond to review feedback promptly
- Be open to suggestions
- Iterate based on feedback

## Adding New Features

Follow test-driven development (TDD):

### 1. Write Tests First

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

### 3. Run Tests

```bash
composer pint
composer test
```

## Common Tasks

### Adding a New Command

1. Create command class in `src/Commands/`
2. Register in `FluxFontAwesomeServiceProvider::boot()`
3. Write feature test
4. Update README with command usage

### Updating Config Schema

1. Update `config/flux-font-awesome.php`
2. Update README configuration section
3. Add migration notes in CHANGELOG if breaking
4. Test with `flux-fa:config` command

### Handling Font Awesome Updates

When Font Awesome releases new variants:
1. Update `Variant` class constants
2. Update `DIRECTORY_MAP`
3. Add tests for new variants
4. Update README documentation

## Debugging Tips

### Testing Commands Manually

Use Orchestra Testbench to simulate Laravel:

```php
public function test_command(): void
{
    $this->artisan('flux-fa:build')
        ->expectsOutput('Building...')
        ->assertSuccessful();
}
```

### Verbose Output

Run commands with `-v` flag:
```bash
php artisan flux-fa:build -v
```

### Inspecting Generated Icons

Check output at:
```
resources/views/flux/icon/fa/
```

## Reporting Issues

When reporting bugs, please include:
- Laravel version
- PHP version
- Font Awesome version (Free or Pro)
- Installation method (NPM or Composer)
- Steps to reproduce
- Expected vs actual behavior
- Error messages or logs

## Feature Requests

Feature requests are welcome! Please:
- Check if it already exists in issues
- Explain the use case clearly
- Provide examples
- Consider backward compatibility

## Performance Considerations

### Icon Building
- `flux-fa:build` scans all SVG files
- For large icon sets (1000+), progress bars keep user informed
- Consider caching strategies for repeated builds

### Runtime Performance
- Icons are static Blade components (zero runtime overhead)
- No database queries
- No API calls
- Pure SVG rendering

## Release Process

For maintainers:

### 1. Update CHANGELOG.md

```markdown
## [1.1.0] - 2025-01-15

### Added
- New feature description

### Fixed
- Bug fix description
```

### 2. Tag Release

```bash
git tag -a v1.1.0 -m "Release version 1.1.0"
git push origin v1.1.0
```

### 3. Create GitHub Release

Include:
- Release notes from CHANGELOG
- Migration guide if needed
- Breaking changes highlighted

## Questions?

- Open a [Discussion](https://github.com/tunr/flux-font-awesome/discussions)
- Check existing [Issues](https://github.com/tunr/flux-font-awesome/issues)
- Review the [README](README.md) for usage documentation

Thank you for contributing! 🙏
