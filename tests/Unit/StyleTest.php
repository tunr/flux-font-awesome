<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Support\Style;

class StyleTest extends TestCase
{
    #[Test]
    public function it_validates_valid_styles(): void
    {
        $this->assertTrue(Style::isValid('solid'));
        $this->assertTrue(Style::isValid('regular'));
        $this->assertTrue(Style::isValid('brands'));
        $this->assertTrue(Style::isValid('light'));
        $this->assertTrue(Style::isValid('thin'));
        $this->assertTrue(Style::isValid('duotone'));
    }

    #[Test]
    public function it_rejects_invalid_styles(): void
    {
        $this->assertFalse(Style::isValid('invalid'));
        $this->assertFalse(Style::isValid('foo'));
        $this->assertFalse(Style::isValid(''));
    }

    #[Test]
    public function it_identifies_free_styles(): void
    {
        $this->assertTrue(Style::isFree('solid'));
        $this->assertTrue(Style::isFree('regular'));
        $this->assertTrue(Style::isFree('brands'));

        $this->assertFalse(Style::isFree('light'));
        $this->assertFalse(Style::isFree('thin'));
        $this->assertFalse(Style::isFree('duotone'));
    }

    #[Test]
    public function it_identifies_pro_styles(): void
    {
        $this->assertTrue(Style::isPro('light'));
        $this->assertTrue(Style::isPro('thin'));
        $this->assertTrue(Style::isPro('duotone'));
        $this->assertTrue(Style::isPro('sharp-solid'));

        $this->assertFalse(Style::isPro('solid'));
        $this->assertFalse(Style::isPro('regular'));
        $this->assertFalse(Style::isPro('brands'));
    }

    #[Test]
    public function it_converts_styles_to_directory_names(): void
    {
        $this->assertEquals('solid', Style::toDirectory('solid'));
        $this->assertEquals('regular', Style::toDirectory('regular'));
        $this->assertEquals('brands', Style::toDirectory('brands'));
        $this->assertEquals('light', Style::toDirectory('light'));
        $this->assertEquals('sharp-solid', Style::toDirectory('sharp-solid'));
    }

    #[Test]
    public function it_normalizes_style_aliases(): void
    {
        $this->assertEquals('solid', Style::normalize('fas'));
        $this->assertEquals('solid', Style::normalize('s'));
        $this->assertEquals('regular', Style::normalize('far'));
        $this->assertEquals('regular', Style::normalize('r'));
        $this->assertEquals('brands', Style::normalize('fab'));
        $this->assertEquals('brands', Style::normalize('b'));
        $this->assertEquals('light', Style::normalize('fal'));
        $this->assertEquals('light', Style::normalize('l'));
        $this->assertEquals('thin', Style::normalize('fat'));
        $this->assertEquals('thin', Style::normalize('t'));
        $this->assertEquals('duotone', Style::normalize('fad'));
        $this->assertEquals('duotone', Style::normalize('d'));
    }

    #[Test]
    public function it_returns_all_styles(): void
    {
        $all = Style::all();

        $this->assertCount(10, $all);
        $this->assertContains('solid', $all);
        $this->assertContains('regular', $all);
        $this->assertContains('brands', $all);
        $this->assertContains('light', $all);
    }

    #[Test]
    public function it_returns_free_styles_only(): void
    {
        $free = Style::all('free');

        $this->assertCount(3, $free);
        $this->assertContains('solid', $free);
        $this->assertContains('regular', $free);
        $this->assertContains('brands', $free);
        $this->assertNotContains('light', $free);
    }

    #[Test]
    public function it_returns_pro_styles_only(): void
    {
        $pro = Style::all('pro');

        $this->assertCount(7, $pro);
        $this->assertContains('light', $pro);
        $this->assertContains('thin', $pro);
        $this->assertContains('duotone', $pro);
        $this->assertNotContains('solid', $pro);
        $this->assertNotContains('regular', $pro);
    }

    #[Test]
    public function it_throws_exception_for_invalid_style_in_normalize(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid style 'invalid'");

        Style::normalize('invalid');
    }

    #[Test]
    public function it_throws_exception_for_path_traversal_in_style(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid style '../etc/passwd'");

        Style::normalize('../etc/passwd');
    }
}
