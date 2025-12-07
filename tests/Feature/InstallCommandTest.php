<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    #[Test]
    public function it_runs_install_command_successfully(): void
    {
        $this->artisan('flux-fa:install')
            ->assertSuccessful();
    }

    #[Test]
    public function it_creates_icon_directory(): void
    {
        $outputPath = config('flux-font-awesome.output_path');

        // Clean up if exists
        if (File::isDirectory($outputPath)) {
            File::deleteDirectory($outputPath);
        }

        $this->artisan('flux-fa:install')->assertSuccessful();

        $this->assertTrue(File::isDirectory($outputPath));

        // Clean up
        File::deleteDirectory($outputPath);
    }
}
