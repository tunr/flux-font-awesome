<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Services\SvgParser;

class SvgParserTest extends TestCase
{
    protected SvgParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SvgParser;
    }

    /** @test */
    public function it_parses_simple_svg(): void
    {
        $svg = '<svg viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M256 0L512 512H0L256 0z"/></svg>';

        $result = $this->parser->parse($svg);

        $this->assertEquals('0 0 512 512', $result['viewBox']);
        $this->assertCount(1, $result['paths']);
        $this->assertEquals('M256 0L512 512H0L256 0z', $result['paths'][0]['d']);
    }

    /** @test */
    public function it_uses_default_viewbox_when_missing(): void
    {
        $svg = '<svg><path d="M0 0"/></svg>';

        $result = $this->parser->parse($svg);

        $this->assertEquals('0 0 512 512', $result['viewBox']);
    }

    /** @test */
    public function it_extracts_multiple_paths(): void
    {
        $svg = '<svg viewBox="0 0 512 512"><path d="M0 0"/><path d="M100 100"/></svg>';

        $result = $this->parser->parse($svg);

        $this->assertCount(2, $result['paths']);
        $this->assertEquals('M0 0', $result['paths'][0]['d']);
        $this->assertEquals('M100 100', $result['paths'][1]['d']);
    }

    /** @test */
    public function it_preserves_path_attributes_for_duotone(): void
    {
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" opacity="0.4" class="fa-secondary"/>
            <path d="M100 100" opacity="1" class="fa-primary"/>
        </svg>';

        $result = $this->parser->parse($svg);

        $this->assertEquals('0.4', $result['paths'][0]['opacity']);
        $this->assertEquals('fa-secondary', $result['paths'][0]['class']);
        $this->assertEquals('1', $result['paths'][1]['opacity']);
        $this->assertEquals('fa-primary', $result['paths'][1]['class']);
    }


    /** @test */
    public function it_generates_path_markup(): void
    {
        $parsedData = [
            'viewBox' => '0 0 512 512',
            'paths' => [
                ['d' => 'M0 0'],
            ],
        ];

        $markup = $this->parser->generatePathMarkup($parsedData);

        $this->assertStringContainsString('<path', $markup);
        $this->assertStringContainsString('d="M0 0"', $markup);
        $this->assertStringContainsString('/>', $markup);
    }

    /** @test */
    public function it_generates_path_markup_with_attributes(): void
    {
        $parsedData = [
            'viewBox' => '0 0 512 512',
            'paths' => [
                [
                    'd' => 'M0 0',
                    'opacity' => '0.4',
                    'class' => 'fa-secondary',
                ],
            ],
        ];

        $markup = $this->parser->generatePathMarkup($parsedData);

        $this->assertStringContainsString('d="M0 0"', $markup);
        $this->assertStringContainsString('opacity="0.4"', $markup);
        $this->assertStringContainsString('class="fa-secondary"', $markup);
    }

    /** @test */
    public function it_throws_exception_for_invalid_svg(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid SVG content');

        $this->parser->parse('<div>Not an SVG</div>');
    }
}
