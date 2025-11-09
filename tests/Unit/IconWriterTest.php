<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Services\SvgParser;
use Tunr\FluxFontAwesome\Tests\TestCase;

class IconWriterTest extends TestCase
{
    protected IconWriter $writer;

    protected string $tempPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->writer = new IconWriter(new Filesystem, new SvgParser);
        $this->tempPath = sys_get_temp_dir().'/fa-test-'.uniqid();
        mkdir($this->tempPath, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempPath)) {
            (new Filesystem)->deleteDirectory($this->tempPath);
        }

        parent::tearDown();
    }

    /** @test */
    public function it_writes_icon_file_successfully(): void
    {
        $svgPath = $this->createTestSvg('user', 'solid');

        $result = $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);

        $this->assertTrue($result);
        $this->assertTrue($this->writer->exists('user', $this->tempPath));
    }

    /** @test */
    public function it_generates_correct_filename(): void
    {
        $svgPath = $this->createTestSvg('github', 'solid');

        $this->writer->write('github', ['solid' => $svgPath], $this->tempPath);

        $this->assertFileExists($this->tempPath.'/fa-github.blade.php');
    }

    /** @test */
    public function it_creates_output_directory_if_not_exists(): void
    {
        $nestedPath = $this->tempPath.'/nested/output';
        $svgPath = $this->createTestSvg('user', 'solid');

        $this->writer->write('user', ['solid' => $svgPath], $nestedPath);

        $this->assertDirectoryExists($nestedPath);
        $this->assertFileExists($nestedPath.'/fa-user.blade.php');
    }

    /** @test */
    public function it_throws_exception_when_no_valid_variants(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No valid variants found');

        $this->writer->write('missing', ['solid' => '/nonexistent.svg'], $this->tempPath);
    }

    /** @test */
    public function it_generates_blade_content_with_viewbox(): void
    {
        $svgPath = $this->createTestSvg('user', 'solid', '0 0 448 512');

        $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);

        $content = file_get_contents($this->tempPath.'/fa-user.blade.php');

        $this->assertStringContainsString('viewBox="0 0 448 512"', $content);
    }

    /** @test */
    public function it_generates_blade_content_with_switch_statement(): void
    {
        $svgPath = $this->createTestSvg('user', 'solid');

        $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);

        $content = file_get_contents($this->tempPath.'/fa-user.blade.php');

        $this->assertStringContainsString('@switch($style)', $content);
        $this->assertStringContainsString("@case('solid')", $content);
        $this->assertStringContainsString('@default', $content);
    }

    /** @test */
    public function it_supports_multiple_variants_for_single_icon(): void
    {
        $solidPath = $this->createTestSvg('heart', 'solid');
        $regularPath = $this->createTestSvg('heart', 'regular');

        $this->writer->write('heart', [
            'solid' => $solidPath,
            'regular' => $regularPath,
        ], $this->tempPath);

        $content = file_get_contents($this->tempPath.'/fa-heart.blade.php');

        $this->assertStringContainsString("@case('solid')", $content);
        $this->assertStringContainsString("@case('regular')", $content);
    }

    /** @test */
    public function it_deletes_existing_icon_file(): void
    {
        $svgPath = $this->createTestSvg('user', 'solid');
        $this->writer->write('user', ['solid' => $svgPath], $this->tempPath);

        $deleted = $this->writer->delete('user', $this->tempPath);

        $this->assertTrue($deleted);
        $this->assertFalse($this->writer->exists('user', $this->tempPath));
    }

    /** @test */
    public function it_returns_false_when_deleting_nonexistent_icon(): void
    {
        $deleted = $this->writer->delete('nonexistent', $this->tempPath);

        $this->assertFalse($deleted);
    }

    /** @test */
    public function it_lists_built_icon_files(): void
    {
        $this->writer->write('user', ['solid' => $this->createTestSvg('user', 'solid')], $this->tempPath);
        $this->writer->write('github', ['solid' => $this->createTestSvg('github', 'solid')], $this->tempPath);

        $icons = $this->writer->getIconFiles($this->tempPath);

        $this->assertCount(2, $icons);
        $this->assertContains('user', $icons);
        $this->assertContains('github', $icons);
    }

    /** @test */
    public function it_returns_empty_array_for_nonexistent_directory(): void
    {
        $icons = $this->writer->getIconFiles('/nonexistent/path');

        $this->assertEmpty($icons);
    }

    /** @test */
    public function it_only_lists_icon_blade_files(): void
    {
        $this->writer->write('user', ['solid' => $this->createTestSvg('user', 'solid')], $this->tempPath);

        // Create non-icon files that should be ignored
        file_put_contents($this->tempPath.'/other-component.blade.php', 'content');
        file_put_contents($this->tempPath.'/readme.txt', 'content');

        $icons = $this->writer->getIconFiles($this->tempPath);

        $this->assertCount(1, $icons);
        $this->assertContains('user', $icons);
        $this->assertNotContains('other-component', $icons);
    }

    protected function createTestSvg(string $iconName, string $variant, string $viewBox = '0 0 512 512'): string
    {
        $dir = $this->tempPath.'/test-svgs/'.$variant;

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $svgPath = $dir.'/'.$iconName.'.svg';
        $svgContent = sprintf(
            '<svg viewBox="%s" xmlns="http://www.w3.org/2000/svg"><path d="M256 0L512 512H0L256 0z"/></svg>',
            $viewBox
        );

        file_put_contents($svgPath, $svgContent);

        return $svgPath;
    }
}
