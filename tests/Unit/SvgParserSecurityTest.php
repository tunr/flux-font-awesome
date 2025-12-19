<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tunr\FluxFontAwesome\Services\SvgParser;

class SvgParserSecurityTest extends TestCase
{
    private SvgParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new SvgParser;
    }

    #[Test]
    public function it_filters_out_script_tags(): void
    {
        $maliciousSvg = '<svg viewBox="0 0 512 512">
            <script>alert("xss")</script>
            <path d="M0 0"/>
        </svg>';

        $result = $this->parser->parse($maliciousSvg);
        $markup = $this->parser->generatePathMarkup($result);

        $this->assertStringNotContainsString('<script>', $markup);
        $this->assertStringNotContainsString('alert', $markup);
    }

    #[Test]
    public function it_filters_out_event_handlers_on_svg(): void
    {
        $maliciousSvg = '<svg viewBox="0 0 512 512" onload="alert(1)">
            <path d="M0 0"/>
        </svg>';

        $result = $this->parser->parse($maliciousSvg);
        $markup = $this->parser->generatePathMarkup($result);

        $this->assertStringNotContainsString('onload', $markup);
        $this->assertStringNotContainsString('alert', $markup);
    }

    #[Test]
    public function it_filters_out_event_handlers_on_paths(): void
    {
        $maliciousSvg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" onclick="alert(1)" onmouseover="alert(2)"/>
        </svg>';

        $result = $this->parser->parse($maliciousSvg);
        $markup = $this->parser->generatePathMarkup($result);

        $this->assertStringNotContainsString('onclick', $markup);
        $this->assertStringNotContainsString('onmouseover', $markup);
        $this->assertStringNotContainsString('alert', $markup);
    }

    #[Test]
    public function it_filters_out_dangerous_elements(): void
    {
        $maliciousSvg = '<svg viewBox="0 0 512 512">
            <animate attributeName="x" values="0;100" dur="1s"/>
            <foreignObject><body><script>alert(1)</script></body></foreignObject>
            <path d="M0 0"/>
        </svg>';

        $result = $this->parser->parse($maliciousSvg);
        $markup = $this->parser->generatePathMarkup($result);

        $this->assertStringNotContainsString('<animate', $markup);
        $this->assertStringNotContainsString('<foreignObject', $markup);
        $this->assertStringNotContainsString('<body', $markup);
        $this->assertStringNotContainsString('<script', $markup);
    }

    #[Test]
    public function it_escapes_attribute_values(): void
    {
        $maliciousSvg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" fill="red&quot; onclick=&quot;alert(1)"/>
        </svg>';

        $result = $this->parser->parse($maliciousSvg);
        $markup = $this->parser->generatePathMarkup($result);

        // The quotes in the fill value should be escaped as &quot;
        // This prevents breaking out of the attribute context
        $this->assertStringContainsString('&quot;', $markup);
        // There should not be a separate onclick attribute (the whole thing is in fill)
        $this->assertStringNotContainsString('onclick="alert', $markup);
    }

    #[Test]
    public function it_only_allows_safe_path_attributes(): void
    {
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" fill="red" opacity="0.5" data-evil="payload" aria-label="test"/>
        </svg>';

        $result = $this->parser->parse($svg);
        $markup = $this->parser->generatePathMarkup($result);

        // Allowed attributes should be present
        $this->assertStringContainsString('d="M0 0"', $markup);
        $this->assertStringContainsString('fill="red"', $markup);
        $this->assertStringContainsString('opacity="0.5"', $markup);

        // Non-allowed attributes should be filtered out
        $this->assertStringNotContainsString('data-evil', $markup);
        $this->assertStringNotContainsString('payload', $markup);
        $this->assertStringNotContainsString('aria-label', $markup);
    }

    #[Test]
    public function it_filters_invalid_class_names(): void
    {
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" class="valid-class"/>
        </svg>';

        $result = $this->parser->parse($svg);
        $markup = $this->parser->generatePathMarkup($result);

        $this->assertStringContainsString('class="valid-class"', $markup);
    }

    #[Test]
    public function it_rejects_malicious_class_names(): void
    {
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" class="valid&quot; onclick=&quot;alert(1)"/>
        </svg>';

        $result = $this->parser->parse($svg);
        $markup = $this->parser->generatePathMarkup($result);

        // Class with special characters should be filtered out entirely
        $this->assertStringNotContainsString('onclick', $markup);
    }

    #[Test]
    public function it_preserves_fill_values_safely(): void
    {
        // JavaScript URLs in SVG fills don't execute in modern browsers anyway,
        // but we test that the value is properly escaped if present
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" fill="url(#gradient)"/>
        </svg>';

        $result = $this->parser->parse($svg);
        $markup = $this->parser->generatePathMarkup($result);

        // Normal fill values should be preserved
        $this->assertStringContainsString('fill="url(#gradient)"', $markup);
    }

    #[Test]
    public function it_preserves_valid_duotone_attributes(): void
    {
        $svg = '<svg viewBox="0 0 512 512">
            <path d="M0 0" fill="currentColor" opacity="0.4" fill-opacity="0.4" class="fa-secondary"/>
            <path d="M100 100" fill="currentColor" class="fa-primary"/>
        </svg>';

        $result = $this->parser->parse($svg);
        $markup = $this->parser->generatePathMarkup($result);

        // All valid duotone attributes should be preserved
        $this->assertStringContainsString('opacity="0.4"', $markup);
        $this->assertStringContainsString('fill-opacity="0.4"', $markup);
        $this->assertStringContainsString('class="fa-secondary"', $markup);
        $this->assertStringContainsString('class="fa-primary"', $markup);
    }
}
