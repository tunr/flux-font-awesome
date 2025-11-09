<?php

namespace Tunr\FluxFontAwesome\Tests\Unit;

use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Tests\TestCase;

class FontAwesomePathResolverTest extends TestCase
{
    protected FontAwesomePathResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = app(FontAwesomePathResolver::class);
    }

    /** @test */
    public function it_returns_explicit_path_when_valid(): void
    {
        $path = __DIR__.'/../fixtures/fontawesome-test';

        // Create a temporary valid path for testing
        if (! is_dir($path.'/svgs')) {
            mkdir($path.'/svgs', 0755, true);
        }

        $resolved = $this->resolver->resolve($path);

        $this->assertEquals($path, $resolved);

        // Cleanup
        rmdir($path.'/svgs');
        rmdir($path);
    }

    /** @test */
    public function it_validates_paths_correctly(): void
    {
        $validPath = __DIR__.'/../fixtures/fontawesome-valid';
        $invalidPath = __DIR__.'/../fixtures/fontawesome-invalid';

        // Create valid path
        if (! is_dir($validPath.'/svgs')) {
            mkdir($validPath.'/svgs', 0755, true);
        }

        // Create invalid path (no svgs directory)
        if (! is_dir($invalidPath)) {
            mkdir($invalidPath, 0755, true);
        }

        // Test with explicit valid path
        $this->assertTrue($this->resolver->isInstalled($validPath));
        $this->assertFalse($this->resolver->isInstalled($invalidPath));

        // Cleanup
        rmdir($validPath.'/svgs');
        rmdir($validPath);
        rmdir($invalidPath);
    }

    /** @test */
    public function it_gets_svgs_path(): void
    {
        $path = __DIR__.'/../fixtures/fontawesome-svgs';

        if (! is_dir($path.'/svgs')) {
            mkdir($path.'/svgs', 0755, true);
        }

        $svgsPath = $this->resolver->getSvgsPath($path);

        $this->assertEquals($path.'/svgs', $svgsPath);

        // Cleanup
        rmdir($path.'/svgs');
        rmdir($path);
    }

    /** @test */
    public function it_detects_free_installation_type(): void
    {
        $this->markTestSkipped('Requires reflection or making detectType public');
    }

    /** @test */
    public function it_detects_pro_installation_type(): void
    {
        $this->markTestSkipped('Requires reflection or making detectType public');
    }
}
