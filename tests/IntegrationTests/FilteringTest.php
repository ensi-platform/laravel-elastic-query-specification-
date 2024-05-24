<?php

use Ensi\LaravelElasticQuery\Contracts\MatchOptions;
use Ensi\LaravelElasticQuery\Contracts\MultiMatchOptions;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTestCase;

uses(IntegrationTestCase::class);

test('root filters', function () {
    $spec = CompositeSpecification::new()
        ->allowedFilters(['code'])
        ->where('active', true);

    $request = [
        'filter' => ['code' => ['tv', 'gloves']],
    ];

    searchQuery($spec, $request)->assertDocumentIds([1]);
});

test('nested', function () {
    $spec = CompositeSpecification::new();
    $spec->nested(
        'offers',
        Specification::new()
            ->allowedFilters(['active'])
            ->where('seller_id', 20)
    );
    $spec->nested(
        'offers',
        Specification::new()
            ->allowedFilters(['active'])
            ->where('seller_id', 90)
    );

    $request = ['filter' => ['active' => true]];

    searchQuery($spec, $request)->assertDocumentIds([328]);
});

test('validate names', function () {
    $spec = CompositeSpecification::new()->allowedFilters(['code', 'active']);
    $request = [
        'filter' => ['code' => 'tv', 'unknown' => 10],
    ];

    expect(fn () => searchQuery($spec, $request))
        ->toThrow(InvalidQueryException::class);
});

test('exists filter', function (bool $value, int $expectedCount) {
    $spec = CompositeSpecification::new()->allowedFilters([
        AllowedFilter::exists('cashback', 'cashback.active'),
    ]);
    $request = ['filter' => ['cashback' => $value]];

    expect(searchQuery($spec, $request)->get())->toHaveCount($expectedCount);
})->with([
    'true' => [true, 4],
    'false' => [false, 2],
]);

test('range filter', function (array $request, array $expectedIds) {
    $spec = CompositeSpecification::new()
        ->allowedFilters([
            AllowedFilter::greater('rating__gt', 'rating'),
            AllowedFilter::greaterOrEqual('rating__gte', 'rating'),
            AllowedFilter::less('rating__lt', 'rating'),
            AllowedFilter::lessOrEqual('rating__lte', 'rating'),
        ]);

    searchQuery($spec, ['filter' => $request])->assertDocumentIds($expectedIds);
})->with([
    'greater' => [['rating__gt' => 7], [150, 405]],
    'greater or equal' => [['rating__gte' => 7], [1, 150, 405]],
    'less' => [['rating__lt' => 5], [319, 471]],
    'less or equal' => [['rating__lte' => 5], [319, 328, 471]],
    'between' => [['rating__lte' => 7, 'rating__gte' => 5], [1, 328]],
]);

test('match filter', function (string $query, ?MatchOptions $options, array $expectedIds) {
    $spec = CompositeSpecification::new()
        ->allowedFilters([
            AllowedFilter::match('name', 'search_name', $options),
        ]);

    searchQuery($spec, ['filter' => ['name' => $query]])->assertDocumentIds($expectedIds);
})->with([
    'single result' => ['water', null, [150]],
    'multiple results' => ['gloves', null, [319, 471]],
    'with options' => ['woter', MatchOptions::make(fuzziness: 'AUTO'), [150]],
]);

test('multi match filter', function (string $query, ?MultiMatchOptions $options, array $expectedIds) {
    $spec = CompositeSpecification::new()
        ->allowedFilters([
            AllowedFilter::multiMatch('name', ['search_name', 'description'], $options),
        ]);

    searchQuery($spec, ['filter' => ['name' => $query]])->assertDocumentIds($expectedIds);
})->with([
    'single result' => ['water', null, [150]],
    'multiple results' => ['gloves', null, [319, 471]],
    'with options' => ['woter', MultiMatchOptions::make(fuzziness: 'AUTO'), [150]],
]);

test('multi match filter priority', function () {
    $spec = CompositeSpecification::new()
        ->allowedFilters([
            AllowedFilter::multiMatch('name', ['search_name^3', 'description']),
        ]);

    searchQuery($spec, ['filter' => ['name' => 'leather']])->assertDocumentOrder([319, 471]);
});

test('multi match filter without fields', function () {
    $spec = CompositeSpecification::new()
        ->allowedFilters([
            AllowedFilter::multiMatch('name'),
        ]);

    searchQuery($spec, ['filter' => ['name' => 'leather']])->assertDocumentOrder([319, 471]);
});
