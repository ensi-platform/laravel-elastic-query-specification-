<?php

use Ensi\LaravelElasticQuerySpecification\Tests\Data\ProductSpecification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests\TestSearchResults;

uses(IntegrationTestCase::class);

test('search query', function () {
    /** @var IntegrationTestCase $this */

    $queryRequest = makeQueryRequest([
        'filter' => ['package' => 'bottle'],
    ]);

    TestSearchResults::make(new ProductSpecification(), $queryRequest)
        ->assertDocumentIds([150, 405]);
});
