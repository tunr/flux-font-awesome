<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use Tunr\FluxFontAwesome\Tests\TestCase;

class InstallCommandTest extends TestCase
{
    /** @test */
    public function it_runs_install_command_successfully(): void
    {
        $this->artisan('fa:install')
            ->expectsOutput('Installing Flux Font Awesome...')
            ->assertSuccessful();
    }

    /** @test */
    public function it_creates_icon_directory(): void
    {
        $outputPath = config('flux-font-awesome.output_path');

        // Clean up if exists
        if (File::isDirectory($outputPath)) {
            File::deleteDirectory($outputPath);
        }

        $this->artisan('fa:install')->assertSuccessful();

        $this->assertTrue(File::isDirectory($outputPath));

        // Clean up
        File::deleteDirectory($outputPath);
    }

    /** @test */
    public function it_displays_next_steps(): void
    {
        $this->artisan('fa:install')
            ->expectsOutputToContain('Next steps:')
            ->expectsOutputToContain('php artisan fa:build')
            ->assertSuccessful();
    }
}
