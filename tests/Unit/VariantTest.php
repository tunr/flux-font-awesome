<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Support\Variant;

class VariantTest extends TestCase
{
    /** @test */
    public function it_validates_valid_variants(): void
    {
        $this->assertTrue(Variant::isValid('solid'));
        $this->assertTrue(Variant::isValid('regular'));
        $this->assertTrue(Variant::isValid('brands'));
        $this->assertTrue(Variant::isValid('light'));
        $this->assertTrue(Variant::isValid('thin'));
        $this->assertTrue(Variant::isValid('duotone'));
    }

    /** @test */
    public function it_rejects_invalid_variants(): void
    {
        $this->assertFalse(Variant::isValid('invalid'));
        $this->assertFalse(Variant::isValid('foo'));
        $this->assertFalse(Variant::isValid(''));
    }

    /** @test */
    public function it_identifies_free_variants(): void
    {
        $this->assertTrue(Variant::isFree('solid'));
        $this->assertTrue(Variant::isFree('regular'));
        $this->assertTrue(Variant::isFree('brands'));

        $this->assertFalse(Variant::isFree('light'));
        $this->assertFalse(Variant::isFree('thin'));
        $this->assertFalse(Variant::isFree('duotone'));
    }

    /** @test */
    public function it_identifies_pro_variants(): void
    {
        $this->assertTrue(Variant::isPro('light'));
        $this->assertTrue(Variant::isPro('thin'));
        $this->assertTrue(Variant::isPro('duotone'));
        $this->assertTrue(Variant::isPro('sharp-solid'));

        $this->assertFalse(Variant::isPro('solid'));
        $this->assertFalse(Variant::isPro('regular'));
        $this->assertFalse(Variant::isPro('brands'));
    }

    /** @test */
    public function it_converts_variants_to_directory_names(): void
    {
        $this->assertEquals('solid', Variant::toDirectory('solid'));
        $this->assertEquals('regular', Variant::toDirectory('regular'));
        $this->assertEquals('brands', Variant::toDirectory('brands'));
        $this->assertEquals('light', Variant::toDirectory('light'));
        $this->assertEquals('sharp-solid', Variant::toDirectory('sharp-solid'));
    }

    /** @test */
    public function it_normalizes_fontawesome_v5_class_name_aliases(): void
    {
        // FontAwesome v5 class name prefixes (fas, far, fab, etc.)
        $this->assertEquals('solid', Variant::normalize('fas'));
        $this->assertEquals('regular', Variant::normalize('far'));
        $this->assertEquals('brands', Variant::normalize('fab'));
        $this->assertEquals('light', Variant::normalize('fal'));
        $this->assertEquals('thin', Variant::normalize('fat'));
        $this->assertEquals('duotone', Variant::normalize('fad'));
        $this->assertEquals('sharp-solid', Variant::normalize('fass'));
        $this->assertEquals('sharp-regular', Variant::normalize('fasr'));
        $this->assertEquals('sharp-light', Variant::normalize('fasl'));
        $this->assertEquals('sharp-thin', Variant::normalize('fast'));
    }

    /** @test */
    public function it_normalizes_fontawesome_v6_long_form_aliases(): void
    {
        // FontAwesome v6 long-form class names (fa-solid, fa-regular, etc.)
        $this->assertEquals('solid', Variant::normalize('fa-solid'));
        $this->assertEquals('regular', Variant::normalize('fa-regular'));
        $this->assertEquals('brands', Variant::normalize('fa-brands'));
        $this->assertEquals('light', Variant::normalize('fa-light'));
        $this->assertEquals('thin', Variant::normalize('fa-thin'));
        $this->assertEquals('duotone', Variant::normalize('fa-duotone'));
        $this->assertEquals('sharp-solid', Variant::normalize('fa-sharp-solid'));
        $this->assertEquals('sharp-regular', Variant::normalize('fa-sharp-regular'));
        $this->assertEquals('sharp-light', Variant::normalize('fa-sharp-light'));
        $this->assertEquals('sharp-thin', Variant::normalize('fa-sharp-thin'));
    }

    /** @test */
    public function it_returns_all_variants(): void
    {
        $all = Variant::all();

        $this->assertCount(10, $all);
        $this->assertContains('solid', $all);
        $this->assertContains('regular', $all);
        $this->assertContains('brands', $all);
        $this->assertContains('light', $all);
    }

    /** @test */
    public function it_returns_free_variants_only(): void
    {
        $free = Variant::all('free');

        $this->assertCount(3, $free);
        $this->assertContains('solid', $free);
        $this->assertContains('regular', $free);
        $this->assertContains('brands', $free);
        $this->assertNotContains('light', $free);
    }

    /** @test */
    public function it_returns_pro_variants_only(): void
    {
        $pro = Variant::all('pro');

        $this->assertCount(7, $pro);
        $this->assertContains('light', $pro);
        $this->assertContains('thin', $pro);
        $this->assertContains('duotone', $pro);
        $this->assertNotContains('solid', $pro);
        $this->assertNotContains('regular', $pro);
    }
}
