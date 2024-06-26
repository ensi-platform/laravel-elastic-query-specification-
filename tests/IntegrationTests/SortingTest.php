<?php

use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Sorting\AllowedSort;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTestCase;

uses(IntegrationTestCase::class);

test('root sort', function () {
    $spec = CompositeSpecification::new()
        ->allowedSorts(['rating'])
        ->where('tags', 'video');

    $request = ['sort' => '-rating'];

    searchQuery($spec, $request)->assertDocumentIds([1, 328]);
});

test('nested sort', function () {
    $nested = Specification::new()
        ->allowedSorts(['+price'])
        ->where('seller_id', 20);

    $spec = CompositeSpecification::new()
        ->where('tags', 'video')
        ->nested('offers', $nested);

    $request = ['sort' => '-price'];

    searchQuery($spec, $request)->assertDocumentOrder([328, 1]);
});

test('complex sort', function () {
    $spec = CompositeSpecification::new()
        ->allowedSorts([AllowedSort::field('-cashback', 'cashback.value')])
        ->allowedFilters([AllowedFilter::exact('cashback', 'cashback.active')])
        ->nested('offers', function (Specification $spec) {
            $spec->allowedSorts(['+price']);
        });

    $request = [
        'filter' => ['cashback' => 'true'],
        'sort' => 'cashback,-price',
    ];

    searchQuery($spec, $request)->assertDocumentOrder([328, 1, 150, 319]);
});

test('validate names', function () {
    $spec = CompositeSpecification::new()->allowedSorts(['rating']);
    $request = ['sort' => ['unknown', 'rating']];

    expect(fn () => searchQuery($spec, $request))
        ->toThrow(InvalidQueryException::class);
});

test('sort missing values', function () {
    $spec = CompositeSpecification::new()->allowedSorts([
        AllowedSort::field('cashback', 'cashback.value')->missingValuesFirst(),
        'product_id',
    ]);

    $request = [
        'sort' => ['cashback', 'product_id'],
    ];

    searchQuery($spec, $request)->assertDocumentOrder([405, 471, 319, 1, 150, 328]);
});
