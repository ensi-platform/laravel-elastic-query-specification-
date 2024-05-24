<?php

use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTestCase;

uses(IntegrationTestCase::class);

test('root aggregating', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()->allowedAggregates(['tags']);
    $request = ['aggregate' => ['tags']];

    aggQuery($spec, $request)
        ->assertBucketKeys('tags', ['water', 'video', 'gloves', 'clothes', 'drinks']);
});

test('nested aggregating', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec->allowedFilters(['active'])
                ->allowedAggregates([AllowedAggregate::minmax('price')])
                ->where('seller_id', 10);
        });

    $request = [
        'filter' => ['active' => false],
        'aggregate' => 'price',
    ];

    aggQuery($spec, $request)->assertMinMax('price', 168.0, 980.0);
});

test('multiple nested', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec->allowedAggregates([AllowedAggregate::minmax('price_active', 'price')])
                ->where('seller_id', 10)
                ->where('active', true);
        })
        ->nested('offers', function (Specification $spec) {
            $spec->allowedAggregates([AllowedAggregate::minmax('price_inactive', 'price')])
                ->where('seller_id', 10)
                ->where('active', false);
        });

    $request = ['aggregate' => 'price_active,price_inactive'];

    aggQuery($spec, $request)
        ->assertMinMax('price_inactive', 168.0, 980.0)
        ->assertMinMax('price_active', 20000.0, 20000.0);
});

test('complex', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec->allowedAggregates([
                'seller_id',
                AllowedAggregate::minmax('price'),
            ]);
        })
        ->nested('offers', function (Specification $spec) {
            $spec->allowedFilters(['active']);
        })
        ->where('tags', 'drinks');

    $request = [
        'aggregate' => ['seller_id', 'price'],
        'filter' => ['active' => true],
    ];

    aggQuery($spec, $request)
        ->assertBucketKeys('seller_id', [10, 15, 20])
        ->assertMinMax('price', 168.0, 210.0);
});

test('validate names', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()->allowedAggregates(['tags']);
    $request = ['aggregate' => ['tags', 'unknown']];

    expect(fn () => aggQuery($spec, $request))->toThrow(InvalidQueryException::class);
});

test('terms with size', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()->allowedAggregates([
        AllowedAggregate::terms('tags', size: 2),
    ]);

    $request = ['aggregate' => ['tags']];

    aggQuery($spec, $request)
        ->assertBucketKeysCount('tags', 2);
});

test('count aggregation', function () {
    /** @var IntegrationTestCase $this */

    $spec = CompositeSpecification::new()->allowedAggregates([
        AllowedAggregate::count('product_count', 'product_id'),
    ]);

    $request = ['aggregate' => ['product_count']];

    aggQuery($spec, $request)
        ->assertValue('product_count', 6);
});
