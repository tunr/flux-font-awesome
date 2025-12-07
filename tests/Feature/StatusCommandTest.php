<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Tests\TestCase;

class StatusCommandTest extends TestCase
{
    #[Test]
    public function it_runs_status_command(): void
    {
        $this->artisan('flux-fa:status')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_font_awesome_status(): void
    {
        $this->artisan('flux-fa:status')
            ->assertSuccessful()
            ->expectsOutputToContain('Font Awesome');
    }

    #[Test]
    public function it_shows_output_directory_status(): void
    {
        $outputPath = config('flux-font-awesome.output_path');

        // Ensure directory exists
        if (! File::isDirectory($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        $this->artisan('flux-fa:status')
            ->assertSuccessful()
            ->expectsOutputToContain('Output');
    }

    #[Test]
    public function it_shows_icon_count(): void
    {
        // Build an icon first
        $this->artisan('flux-fa:build', ['icons' => ['user']]);

        $this->artisan('flux-fa:status')
            ->assertSuccessful()
            ->expectsOutputToContain('Icons');
    }
}
