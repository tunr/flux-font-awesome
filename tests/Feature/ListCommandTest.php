<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Tests\TestCase;

class ListCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean and prepare output directory
        $outputPath = config('flux-font-awesome.output_path');
        if (File::isDirectory($outputPath)) {
            File::cleanDirectory($outputPath);
        } else {
            File::makeDirectory($outputPath, 0755, true);
        }
    }

    #[Test]
    public function it_shows_no_icons_message_when_empty(): void
    {
        $this->artisan('flux-fa:list')
            ->assertSuccessful()
            ->expectsOutputToContain('No icons found');
    }

    #[Test]
    public function it_lists_built_icons(): void
    {
        // Build some icons first
        $this->artisan('flux-fa:build', ['icons' => ['user']]);

        $this->artisan('flux-fa:list')
            ->assertSuccessful()
            ->expectsOutputToContain('user');
    }

    #[Test]
    public function it_outputs_json_when_flag_is_set(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user']]);

        $this->artisan('flux-fa:list', ['--json' => true])
            ->assertSuccessful();
    }

    #[Test]
    public function it_outputs_empty_json_when_no_icons(): void
    {
        $this->artisan('flux-fa:list', ['--json' => true])
            ->assertSuccessful();
    }
}
