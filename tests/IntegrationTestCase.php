<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests;

use Ensi\LaravelElasticQuery\ElasticQuery;
use Ensi\LaravelElasticQuerySpecification\Tests\Data\Seeds\ProductIndexSeeder;

class IntegrationTestCase extends TestCase
{
    public function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        config()->set('tests.recreate_index', env('RECREATE_INDEX', true));
    }

    protected function setUp(): void
    {
        parent::setUp();

        ProductIndexSeeder::run();
    }

    protected function tearDown(): void
    {
        ElasticQuery::disableQueryLog();

        parent::tearDown();
    }
}
