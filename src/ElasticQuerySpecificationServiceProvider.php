<?php

namespace Ensi\LaravelElasticQuerySpecification;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ElasticQuerySpecificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-elastic-query-specification.php', 'laravel-elastic-query-specification');

        $this->app->bind(
            QueryBuilderRequest::class,
            fn (Application $app) => QueryBuilderRequest::fromRequest($app['request'])
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-elastic-query-specification.php' => config_path('laravel-elastic-query-specification.php'),
            ], 'config');
        }
    }
}
