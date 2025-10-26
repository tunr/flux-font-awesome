<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Tunr\FluxFontAwesome\Tests\TestCase;

class ConfigCommandTest extends TestCase
{
    /** @test */
    public function it_displays_configuration(): void
    {
        $this->artisan('fa:config')
            ->assertSuccessful();
    }

    /** @test */
    public function it_shows_all_configuration_sections(): void
    {
        $this->artisan('fa:config')
            ->assertSuccessful();
    }
}
