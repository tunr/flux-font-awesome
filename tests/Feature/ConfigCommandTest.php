<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Tunr\FluxFontAwesome\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    /** @test */
    public function it_displays_configuration(): void
    {
        $this->artisan('fa:config')
            ->expectsOutput('Flux Font Awesome Configuration')
            ->expectsOutputToContain('Paths:')
            ->expectsOutputToContain('Defaults:')
            ->expectsOutputToContain('Source Path')
            ->expectsOutputToContain('Output Path')
            ->expectsOutputToContain('Default Variant')
            ->assertSuccessful();
    }

    /** @test */
    public function it_shows_all_configuration_sections(): void
    {
        $this->artisan('fa:config')
            ->expectsOutputToContain('Sizes:')
            ->expectsOutputToContain('Build Settings:')
            ->expectsOutputToContain('Duotone:')
            ->assertSuccessful();
    }
}
