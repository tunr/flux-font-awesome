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
    public function it_builds_with_specific_variant(): void
    {
        $this->artisan('flux-fa:build', [
            'icons' => ['user'],
            '--variant' => ['solid'],
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
}
