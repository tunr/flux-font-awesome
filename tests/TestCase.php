<?php

namespace Tunr\FluxFontAwesome\Tests;

use Flux\FluxServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tunr\FluxFontAwesome\FluxFontAwesomeServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            FluxServiceProvider::class,
            FluxFontAwesomeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set up test configuration
        config()->set('flux-font-awesome.source_path', __DIR__.'/fixtures/fontawesome');
        config()->set('flux-font-awesome.output_path', __DIR__.'/fixtures/output');
    }
}
