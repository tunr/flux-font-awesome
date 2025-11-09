# Missing Tests - Practical Examples

This document shows what practical, meaningful tests would look like for the untested components. Focus is on testing behavior that matters, not implementation details.

## 1. IconHelperTest (HIGH PRIORITY)

**What to test**: The core helper that resolves styles, sizes, and scaling for icons.

```php
<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Support\IconHelper;

class IconHelperTest extends TestCase
{
    /** @test */
    public function it_uses_configured_default_style(): void
    {
        config(['flux-font-awesome.default_variant' => 'regular']);

        $this->assertEquals('regular', IconHelper::resolveStyle(null));
    }

    /** @test */
    public function it_uses_provided_style_over_default(): void
    {
        config(['flux-font-awesome.default_variant' => 'regular']);

        $this->assertEquals('solid', IconHelper::resolveStyle('solid'));
    }

    /** @test */
    public function it_resolves_flux_variant_sizes_correctly(): void
    {
        $this->assertEquals('[:where(&)]:size-6', IconHelper::resolveSizeClass('solid', null));
        $this->assertEquals('[:where(&)]:size-5', IconHelper::resolveSizeClass('mini', null));
        $this->assertEquals('[:where(&)]:size-4', IconHelper::resolveSizeClass('micro', null));
    }

    /** @test */
    public function it_uses_fa_size_when_no_flux_variant(): void
    {
        config(['flux-font-awesome.sizes' => ['sm' => 'size-4', 'md' => 'size-6']]);

        $result = IconHelper::resolveSizeClass(null, 'sm');

        $this->assertStringContainsString('size-4', $result);
    }

    /** @test */
    public function it_returns_empty_string_for_default_scale(): void
    {
        config(['flux-font-awesome.icon_scale' => 1.0]);

        $this->assertEquals('', IconHelper::getScaleStyle());
    }

    /** @test */
    public function it_generates_scale_style_when_configured(): void
    {
        config(['flux-font-awesome.icon_scale' => 1.2]);

        $result = IconHelper::getScaleStyle();

        $this->assertStringContainsString('transform: scale(1.2)', $result);
    }
}
```

**Why these tests matter**: IconHelper is used by every generated icon component. If size calculation or style resolution breaks, all icons break.

---

## 2. IconWriterTest (HIGH PRIORITY)

**What to test**: The service that writes icon Blade components to disk.

```php
<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Services\SvgParser;
use Tunr\FluxFontAwesome\Tests\TestCase;

class IconWriterTest extends TestCase
{
    protected IconWriter $writer;
    protected string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = new IconWriter(new Filesystem(), new SvgParser());
        $this->tempPath = sys_get_temp_dir().'/fa-test-'.uniqid();
        mkdir($this->tempPath, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempPath)) {
            (new Filesystem())->deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_writes_icon_file_successfully(): void
    {
        $svgPath = __DIR__.'/../fixtures/fontawesome-valid/svgs/solid/user.svg';

        // Create a test SVG file
        if (!is_dir(dirname($svgPath))) {
            mkdir(dirname($svgPath), 0755, true);
        }
        file_put_contents($svgPath, '<svg viewBox="0 0 512 512"><path d="M0 0"/></svg>');

        $result = $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);

        $this->assertTrue($result);
        $this->assertTrue($this->writer->exists('user', $this->tempPath));
    }

    /** @test */
    public function it_generates_correct_filename(): void
    {
        $svgPath = __DIR__.'/../fixtures/fontawesome-valid/svgs/solid/github.svg';
        if (!is_dir(dirname($svgPath))) {
            mkdir(dirname($svgPath), 0755, true);
        }
        file_put_contents($svgPath, '<svg viewBox="0 0 512 512"><path d="M0 0"/></svg>');

        $this->writer->write('github', ['solid' => $svgPath], $this->tempPath);

        $this->assertFileExists($this->tempPath.'/fa-github.blade.php');
    }

    /** @test */
    public function it_throws_exception_when_no_valid_variants(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No valid variants found');

        $this->writer->write('missing', ['solid' => '/nonexistent.svg'], $this->tempPath);
    }

    /** @test */
    public function it_lists_built_icon_files(): void
    {
        $svgPath = __DIR__.'/../fixtures/fontawesome-valid/svgs/solid/user.svg';
        if (!is_dir(dirname($svgPath))) {
            mkdir(dirname($svgPath), 0755, true);
        }
        file_put_contents($svgPath, '<svg viewBox="0 0 512 512"><path d="M0 0"/></svg>');

        $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);
        $this->writer->write('github', ['solid' => $svgPath], $this->tempPath);

        $icons = $this->writer->getIconFiles($this->tempPath);

        $this->assertCount(2, $icons);
        $this->assertContains('user', $icons);
        $this->assertContains('github', $icons);
    }
}
```

**Why these tests matter**: IconWriter is responsible for generating the actual Blade files. If it breaks, the build command produces broken files.

---

## 3. BuildCommandTest (MEDIUM PRIORITY)

**What to test**: The main build command behavior and error handling.

```php
<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\FluxFontAwesomeServiceProvider;
use Tunr\FluxFontAwesome\Tests\TestCase;

class BuildCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test fixture SVGs
        $fixturePath = __DIR__.'/../fixtures/fontawesome/svgs';

        foreach (['solid', 'regular'] as $variant) {
            $variantPath = $fixturePath.'/'.$variant;
            if (!is_dir($variantPath)) {
                mkdir($variantPath, 0755, true);
            }

            // Create test icons
            file_put_contents(
                $variantPath.'/user.svg',
                '<svg viewBox="0 0 512 512"><path d="M0 0"/></svg>'
            );
            file_put_contents(
                $variantPath.'/github.svg',
                '<svg viewBox="0 0 496 512"><path d="M165 0"/></svg>'
            );
        }
    }

    /** @test */
    public function it_builds_specific_icons_when_provided(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user']])
            ->assertSuccessful();

        $outputPath = resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH);

        $this->assertFileExists($outputPath.'/fa-user.blade.php');
    }

    /** @test */
    public function it_fails_gracefully_when_font_awesome_not_installed(): void
    {
        config(['flux-font-awesome.source_path' => '/nonexistent/path']);

        $this->artisan('flux-fa:build')
            ->assertFailed()
            ->expectsOutput('Font Awesome installation not found');
    }

    /** @test */
    public function it_cleans_existing_icons_when_requested(): void
    {
        $outputPath = resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH);

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0755, true);
        }

        // Create a dummy existing file
        file_put_contents($outputPath.'/fa-old.blade.php', 'old content');

        $this->artisan('flux-fa:build', ['icons' => ['user'], '--clean' => true])
            ->assertSuccessful();

        $this->assertFileDoesNotExist($outputPath.'/fa-old.blade.php');
        $this->assertFileExists($outputPath.'/fa-user.blade.php');
    }

    /** @test */
    public function it_builds_only_requested_variants(): void
    {
        $this->artisan('flux-fa:build', [
            'icons' => ['user'],
            '--variant' => ['solid']
        ])->assertSuccessful();

        $outputPath = resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH);
        $content = file_get_contents($outputPath.'/fa-user.blade.php');

        $this->assertStringContainsString("@case('solid')", $content);
    }
}
```

**Why these tests matter**: Validates the most common workflows users will run. Ensures error messages are helpful.

---

## 4. Fix Existing Failing Tests (HIGH PRIORITY)

### ConfigCommandTest - Fix command name

```php
<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Tunr\FluxFontAwesome\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    /** @test */
    public function it_displays_configuration(): void
    {
        // Changed from 'fa:config' to 'flux-fa:config'
        $this->artisan('flux-fa:config')
            ->assertSuccessful()
            ->expectsOutput('Flux Font Awesome Configuration');
    }

    /** @test */
    public function it_shows_key_configuration_values(): void
    {
        $this->artisan('flux-fa:config')
            ->assertSuccessful()
            ->expectsOutputToContain('Default Variant')
            ->expectsOutputToContain('Default Size');
    }
}
```

### FontAwesomePathResolverTest - Use proper TestCase

```php
<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

// Change from PHPUnit\Framework\TestCase to our TestCase
use Tunr\FluxFontAwesome\Tests\TestCase;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;

class FontAwesomePathResolverTest extends TestCase
{
    protected FontAwesomePathResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(FontAwesomePathResolver::class);
    }

    // ... rest of tests remain the same
}
```

---

## What NOT to Test

Skip these low-value tests:

1. **Don't test configuration values** - Testing that config files return config values is pointless
2. **Don't test framework features** - Testing that Artisan commands exist or File facade works
3. **Don't test private methods** - Test public behavior, not implementation
4. **Don't test trivial getters/setters** - No value in testing `getName()` returns a name
5. **Don't mock everything** - Use real services when practical, only mock external dependencies

---

## Test Priority

**Do first** (fixes existing issues):
1. Fix ConfigCommandTest command names
2. Fix FontAwesomePathResolverTest to use proper TestCase

**Do second** (critical missing coverage):
3. IconHelperTest - Used by every icon
4. IconWriterTest - Core build functionality

**Do if you have time**:
5. BuildCommandTest - Validates end-user workflows
6. Integration test with real Font Awesome fixtures

**Skip for now**:
- StatusCommand tests (mostly display logic)
- Service provider tests (boilerplate)
- Deep edge cases unless they've caused real bugs

---

## Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/Unit/IconHelperTest.php

# Run with coverage
composer test:coverage

# Watch mode (if you add a watcher)
vendor/bin/phpunit --testdox
```
