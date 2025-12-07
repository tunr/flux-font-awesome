<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Support\Variant;

class VariantTest extends TestCase
{
    #[Test]
    public function it_validates_valid_variants(): void
    {
        $this->assertTrue(Variant::isValid('solid'));
        $this->assertTrue(Variant::isValid('regular'));
        $this->assertTrue(Variant::isValid('brands'));
        $this->assertTrue(Variant::isValid('light'));
        $this->assertTrue(Variant::isValid('thin'));
        $this->assertTrue(Variant::isValid('duotone'));
    }

    #[Test]
    public function it_rejects_invalid_variants(): void
    {
        $this->assertFalse(Variant::isValid('invalid'));
        $this->assertFalse(Variant::isValid('foo'));
        $this->assertFalse(Variant::isValid(''));
    }

    #[Test]
    public function it_identifies_free_variants(): void
    {
        $this->assertTrue(Variant::isFree('solid'));
        $this->assertTrue(Variant::isFree('regular'));
        $this->assertTrue(Variant::isFree('brands'));

        $this->assertFalse(Variant::isFree('light'));
        $this->assertFalse(Variant::isFree('thin'));
        $this->assertFalse(Variant::isFree('duotone'));
    }

    #[Test]
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

    #[Test]
    public function it_converts_variants_to_directory_names(): void
    {
        $this->assertEquals('solid', Variant::toDirectory('solid'));
        $this->assertEquals('regular', Variant::toDirectory('regular'));
        $this->assertEquals('brands', Variant::toDirectory('brands'));
        $this->assertEquals('light', Variant::toDirectory('light'));
        $this->assertEquals('sharp-solid', Variant::toDirectory('sharp-solid'));
    }

    #[Test]
    public function it_normalizes_variant_aliases(): void
    {
        $this->assertEquals('solid', Variant::normalize('fas'));
        $this->assertEquals('solid', Variant::normalize('s'));
        $this->assertEquals('regular', Variant::normalize('far'));
        $this->assertEquals('regular', Variant::normalize('r'));
        $this->assertEquals('brands', Variant::normalize('fab'));
        $this->assertEquals('brands', Variant::normalize('b'));
        $this->assertEquals('light', Variant::normalize('fal'));
        $this->assertEquals('light', Variant::normalize('l'));
        $this->assertEquals('thin', Variant::normalize('fat'));
        $this->assertEquals('thin', Variant::normalize('t'));
        $this->assertEquals('duotone', Variant::normalize('fad'));
        $this->assertEquals('duotone', Variant::normalize('d'));
    }

    #[Test]
    public function it_returns_all_variants(): void
    {
        $all = Variant::all();

        $this->assertCount(10, $all);
        $this->assertContains('solid', $all);
        $this->assertContains('regular', $all);
        $this->assertContains('brands', $all);
        $this->assertContains('light', $all);
    }

    #[Test]
    public function it_returns_free_variants_only(): void
    {
        $free = Variant::all('free');

        $this->assertCount(3, $free);
        $this->assertContains('solid', $free);
        $this->assertContains('regular', $free);
        $this->assertContains('brands', $free);
        $this->assertNotContains('light', $free);
    }

    #[Test]
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
