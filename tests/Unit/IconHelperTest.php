<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use Tunr\FluxFontAwesome\Support\IconHelper;
use Tunr\FluxFontAwesome\Tests\TestCase;

class IconHelperTest extends TestCase
{
    /** @test */
    public function it_uses_configured_default_style(): void
    {
        config(['flux-font-awesome.default_variant' => 'regular']);

        $this->assertEquals('regular', IconHelper::resolveStyle(null));
    }

    /** @test */
    public function it_uses_provided_style_over_default(): void
    {
        config(['flux-font-awesome.default_variant' => 'regular']);

        $this->assertEquals('solid', IconHelper::resolveStyle('solid'));
    }

    /** @test */
    public function it_uses_configured_default_size(): void
    {
        config(['flux-font-awesome.default_size' => 'lg']);

        $this->assertEquals('lg', IconHelper::resolveSize(null));
    }

    /** @test */
    public function it_uses_provided_size_over_default(): void
    {
        config(['flux-font-awesome.default_size' => 'lg']);

        $this->assertEquals('sm', IconHelper::resolveSize('sm'));
    }

    /** @test */
    public function it_resolves_flux_variant_sizes_correctly(): void
    {
        $this->assertEquals('[:where(&)]:size-6', IconHelper::resolveSizeClass('solid', null));
        $this->assertEquals('[:where(&)]:size-6', IconHelper::resolveSizeClass('outline', null));
        $this->assertEquals('[:where(&)]:size-5', IconHelper::resolveSizeClass('mini', null));
        $this->assertEquals('[:where(&)]:size-4', IconHelper::resolveSizeClass('micro', null));
    }

    /** @test */
    public function it_uses_fa_size_when_no_flux_variant(): void
    {
        config(['flux-font-awesome.sizes' => ['sm' => 'size-4', 'md' => 'size-6']]);

        $result = IconHelper::resolveSizeClass(null, 'sm');

        $this->assertStringContainsString('size-4', $result);
        $this->assertStringContainsString('[:where(&)]:', $result);
    }

    /** @test */
    public function it_falls_back_to_md_size_when_size_not_found(): void
    {
        config([
            'flux-font-awesome.default_size' => 'unknown',
            'flux-font-awesome.sizes' => ['md' => 'size-6'],
        ]);

        $result = IconHelper::resolveSizeClass(null, null);

        $this->assertStringContainsString('size-6', $result);
    }

    /** @test */
    public function it_returns_empty_string_for_default_scale(): void
    {
        config(['flux-font-awesome.icon_scale' => 1.0]);

        $this->assertEquals('', IconHelper::getScaleStyle());
    }

    /** @test */
    public function it_generates_scale_style_when_configured(): void
    {
        config(['flux-font-awesome.icon_scale' => 1.2]);

        $result = IconHelper::getScaleStyle();

        $this->assertStringContainsString('style=', $result);
        $this->assertStringContainsString('transform: scale(1.2)', $result);
        $this->assertStringContainsString('transform-origin: center center', $result);
    }

    /** @test */
    public function it_handles_scale_less_than_one(): void
    {
        config(['flux-font-awesome.icon_scale' => 0.8]);

        $result = IconHelper::getScaleStyle();

        $this->assertStringContainsString('scale(0.8)', $result);
    }
}
