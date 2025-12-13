<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Tests\TestCase;

class BuildCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean output directory before each test
        $outputPath = config('flux-font-awesome.output_path');
        if (File::isDirectory($outputPath)) {
            File::cleanDirectory($outputPath);
        }
    }

    #[Test]
    public function it_builds_single_icon(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user']])
            ->assertSuccessful();

        $outputPath = config('flux-font-awesome.output_path');
        $this->assertFileExists($outputPath.'/fa-user.blade.php');
    }

    #[Test]
    public function it_builds_multiple_icons(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user', 'github']])
            ->assertSuccessful();

        $outputPath = config('flux-font-awesome.output_path');
        $this->assertFileExists($outputPath.'/fa-user.blade.php');
        $this->assertFileExists($outputPath.'/fa-github.blade.php');
    }

    #[Test]
    public function it_builds_with_specific_style(): void
    {
        $this->artisan('flux-fa:build', [
            'icons' => ['user'],
            '--style' => ['solid'],
        ])->assertSuccessful();

        $outputPath = config('flux-font-awesome.output_path');
        $content = File::get($outputPath.'/fa-user.blade.php');

        $this->assertStringContainsString("@case('solid')", $content);
    }

    #[Test]
    public function it_handles_nonexistent_icon_gracefully(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['nonexistent-icon-xyz']])
            ->assertSuccessful();

        // Should complete without creating file for nonexistent icon
        $outputPath = config('flux-font-awesome.output_path');
        $this->assertFileDoesNotExist($outputPath.'/fa-nonexistent-icon-xyz.blade.php');
    }

    #[Test]
    public function it_builds_brands_icon_with_only_brands_style(): void
    {
        // Build github (a brands-only icon) even with solid style specified
        $this->artisan('flux-fa:build', [
            'icons' => ['github'],
            '--style' => ['solid'],
        ])->assertSuccessful();

        $outputPath = config('flux-font-awesome.output_path');
        $this->assertFileExists($outputPath.'/fa-github.blade.php');

        // Should only contain brands style, not solid
        $content = File::get($outputPath.'/fa-github.blade.php');
        $this->assertStringContainsString("@case('brands')", $content);
        $this->assertStringNotContainsString("@case('solid')", $content);
    }

    #[Test]
    public function it_builds_mixed_icons_with_correct_styles(): void
    {
        // Build user (regular icon) and github (brands icon) with solid style
        $this->artisan('flux-fa:build', [
            'icons' => ['user', 'github'],
            '--style' => ['solid'],
        ])->assertSuccessful();

        $outputPath = config('flux-font-awesome.output_path');

        // User should have solid style
        $userContent = File::get($outputPath.'/fa-user.blade.php');
        $this->assertStringContainsString("@case('solid')", $userContent);

        // GitHub should have brands style (auto-detected)
        $githubContent = File::get($outputPath.'/fa-github.blade.php');
        $this->assertStringContainsString("@case('brands')", $githubContent);
        $this->assertStringNotContainsString("@case('solid')", $githubContent);
    }
}
