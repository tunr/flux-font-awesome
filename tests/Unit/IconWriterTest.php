<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\Test;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Services\SvgParser;
use Tunr\FluxFontAwesome\Tests\TestCase;

class IconWriterTest extends TestCase
{
    protected IconWriter $writer;

    protected string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = new IconWriter(new Filesystem, new SvgParser);
        $this->outputPath = __DIR__.'/../fixtures/output';

        // Ensure output directory exists
        if (! is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up any created files
        $files = glob($this->outputPath.'/*.php');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function it_writes_icon_with_single_variant(): void
    {
        $variantPaths = [
            'solid' => __DIR__.'/../fixtures/fontawesome/svgs/solid/user.svg',
        ];

        $result = $this->writer->write('user', $variantPaths, $this->outputPath);

        $this->assertTrue($result);
        $this->assertFileExists($this->outputPath.'/fa-user.blade.php');
    }

    #[Test]
    public function it_writes_icon_with_multiple_variants(): void
    {
        $variantPaths = [
            'solid' => __DIR__.'/../fixtures/fontawesome/svgs/solid/user.svg',
            'regular' => __DIR__.'/../fixtures/fontawesome/svgs/regular/user.svg',
        ];

        $result = $this->writer->write('user', $variantPaths, $this->outputPath);

        $this->assertTrue($result);

        $content = file_get_contents($this->outputPath.'/fa-user.blade.php');
        $this->assertStringContainsString("@case('solid')", $content);
        $this->assertStringContainsString("@case('regular')", $content);
    }

    #[Test]
    public function it_checks_if_icon_exists(): void
    {
        $variantPaths = [
            'solid' => __DIR__.'/../fixtures/fontawesome/svgs/solid/user.svg',
        ];

        $this->assertFalse($this->writer->exists('user', $this->outputPath));

        $this->writer->write('user', $variantPaths, $this->outputPath);

        $this->assertTrue($this->writer->exists('user', $this->outputPath));
    }

    #[Test]
    public function it_deletes_icon(): void
    {
        $variantPaths = [
            'solid' => __DIR__.'/../fixtures/fontawesome/svgs/solid/user.svg',
        ];

        $this->writer->write('user', $variantPaths, $this->outputPath);
        $this->assertTrue($this->writer->exists('user', $this->outputPath));

        $result = $this->writer->delete('user', $this->outputPath);

        $this->assertTrue($result);
        $this->assertFalse($this->writer->exists('user', $this->outputPath));
    }

    #[Test]
    public function it_returns_false_when_deleting_nonexistent_icon(): void
    {
        $result = $this->writer->delete('nonexistent', $this->outputPath);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_gets_icon_files(): void
    {
        $this->writer->write('user', [
            'solid' => __DIR__.'/../fixtures/fontawesome/svgs/solid/user.svg',
        ], $this->outputPath);

        $this->writer->write('github', [
            'brands' => __DIR__.'/../fixtures/fontawesome/svgs/brands/github.svg',
        ], $this->outputPath);

        $icons = $this->writer->getIconFiles($this->outputPath);

        $this->assertCount(2, $icons);
        $this->assertContains('user', $icons);
        $this->assertContains('github', $icons);
    }

    #[Test]
    public function it_returns_empty_array_for_nonexistent_directory(): void
    {
        $icons = $this->writer->getIconFiles('/nonexistent/path');

        $this->assertEquals([], $icons);
    }

    #[Test]
    public function it_throws_exception_for_invalid_variant_paths(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No valid variants found');

        $this->writer->write('test', [
            'solid' => '/nonexistent/path.svg',
        ], $this->outputPath);
    }
}
