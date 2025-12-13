<?php

declare(strict_types=1);

namespace Tunr\FluxFontAwesome;

use Illuminate\Support\ServiceProvider;
use Tunr\FluxFontAwesome\Commands\BuildCommand;
use Tunr\FluxFontAwesome\Commands\InstallCommand;
use Tunr\FluxFontAwesome\Commands\ListCommand;
use Tunr\FluxFontAwesome\Commands\RebuildCommand;
use Tunr\FluxFontAwesome\Commands\StatusCommand;
use Tunr\FluxFontAwesome\Services\FontAwesomePathResolver;
use Tunr\FluxFontAwesome\Services\IconWriter;
use Tunr\FluxFontAwesome\Services\SvgParser;

class FluxFontAwesomeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/flux-font-awesome.php',
            'flux-font-awesome'
        );

        // Register services
        $this->app->singleton(FontAwesomePathResolver::class);
        $this->app->singleton(SvgParser::class);
        $this->app->singleton(IconWriter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/flux-font-awesome.php' => config_path('flux-font-awesome.php'),
        ], 'flux-font-awesome-config');

        // Publish views (icon components)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views'),
        ], 'flux-font-awesome-views');

        // Publish AI guidelines
        $this->publishes([
            __DIR__.'/../resources/guidelines/flux-font-awesome.md' => base_path('.ai/guidelines/flux-font-awesome.md'),
        ], 'flux-font-awesome-guidelines');

        // Load views from package
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-font-awesome');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                BuildCommand::class,
                RebuildCommand::class,
                ListCommand::class,
                StatusCommand::class,
            ]);
        }
    }
}
