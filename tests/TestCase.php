<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests;

use Ensi\LaravelElasticQuery\ElasticQueryServiceProvider;
use Ensi\LaravelElasticQuerySpecification\ElasticQuerySpecificationServiceProvider;
use Ensi\LaravelElasticQuerySpecification\Tests\Data\Stubs\CallableStub;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ElasticQueryServiceProvider::class,
            ElasticQuerySpecificationServiceProvider::class,
        ];
    }

    protected function mockCallable(): MockInterface|callable
    {
        return $this->mock(CallableStub::class);
    }
}
