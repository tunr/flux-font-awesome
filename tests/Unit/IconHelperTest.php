<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Support\IconHelper;
use Tunr\FluxFontAwesome\Tests\TestCase;

class IconHelperTest extends TestCase
{
    #[Test]
    public function it_resolves_style_with_default(): void
    {
        $this->assertEquals('solid', IconHelper::resolveStyle(null));
    }

    #[Test]
    public function it_resolves_style_with_explicit_value(): void
    {
        $this->assertEquals('regular', IconHelper::resolveStyle('regular'));
        $this->assertEquals('brands', IconHelper::resolveStyle('brands'));
    }

    #[Test]
    public function it_resolves_size_with_default(): void
    {
        $this->assertEquals('md', IconHelper::resolveSize(null));
    }

    #[Test]
    public function it_resolves_size_with_explicit_value(): void
    {
        $this->assertEquals('lg', IconHelper::resolveSize('lg'));
        $this->assertEquals('sm', IconHelper::resolveSize('sm'));
    }

    #[Test]
    public function it_resolves_size_class_for_flux_variants(): void
    {
        $this->assertEquals('[:where(&)]:size-6', IconHelper::resolveSizeClass('outline', null));
        $this->assertEquals('[:where(&)]:size-6', IconHelper::resolveSizeClass('solid', null));
        $this->assertEquals('[:where(&)]:size-5', IconHelper::resolveSizeClass('mini', null));
        $this->assertEquals('[:where(&)]:size-4', IconHelper::resolveSizeClass('micro', null));
    }

    #[Test]
    public function it_falls_back_to_fa_size_when_no_flux_variant(): void
    {
        // When no Flux variant, should use FA size with low specificity wrapper
        $result = IconHelper::resolveSizeClass(null, 'md');
        $this->assertStringStartsWith('[:where(&)]:', $result);
    }

    #[Test]
    public function it_returns_empty_scale_style_when_scale_is_default(): void
    {
        config()->set('flux-font-awesome.icon_scale', 1.0);
        $this->assertEquals('', IconHelper::getScaleStyle());
    }

    #[Test]
    public function it_returns_scale_style_when_configured(): void
    {
        config()->set('flux-font-awesome.icon_scale', 0.85);
        $style = IconHelper::getScaleStyle();

        $this->assertStringContainsString('transform: scale(0.85)', $style);
        $this->assertStringContainsString('style=', $style);
    }
}
