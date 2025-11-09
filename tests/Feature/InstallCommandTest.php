<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\FluxFontAwesomeServiceProvider;
use Tunr\FluxFontAwesome\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    public function it_runs_install_command_successfully(): void
    {
        $this->artisan('flux-fa:install')
            ->assertSuccessful();
    }

    /** @test */
    public function it_creates_icon_directory(): void
    {
        $outputPath = resource_path(FluxFontAwesomeServiceProvider::FLUX_ICON_PATH);

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
