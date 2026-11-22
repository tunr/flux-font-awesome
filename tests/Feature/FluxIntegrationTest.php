<?php

namespace Tunr\FluxFontAwesome\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Tests\TestCase;

class FluxIntegrationTest extends TestCase
{
    private string $componentRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->componentRoot = storage_path('framework/testing/flux-font-awesome');

        File::deleteDirectory($this->componentRoot);
        File::ensureDirectoryExists($this->componentRoot.'/icon');

        config()->set('flux-font-awesome.output_path', $this->componentRoot.'/icon');
        Blade::anonymousComponentPath($this->componentRoot, 'flux');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->componentRoot);

        parent::tearDown();
    }

    #[Test]
    public function a_generated_icon_renders_as_a_flux_compatible_blade_component(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user']])
            ->assertSuccessful();

        $html = Blade::render('<flux:icon.fa-user class="text-red-500" />');

        $this->assertStringContainsString('data-flux-icon', $html);
        $this->assertStringContainsString('text-red-500', $html);
        $this->assertStringContainsString('viewBox="0 0 448 512"', $html);
    }

    #[Test]
    public function a_generated_icon_renders_through_a_flux_component_icon_prop(): void
    {
        $this->artisan('flux-fa:build', ['icons' => ['user']])
            ->assertSuccessful();

        $html = Blade::render('<flux:button icon="fa-user">Save</flux:button>');

        $this->assertStringContainsString('data-flux-icon', $html);
        $this->assertStringContainsString('Save', $html);
    }
}
