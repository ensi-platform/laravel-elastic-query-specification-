<?php

use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTestCase;

uses(IntegrationTestCase::class);

test('facets in root specification', function () {
    $spec = CompositeSpecification::new()
        ->allowedFacets([
            AllowedFacet::minmax('rating'),
            AllowedFacet::terms('tags'),
        ])
        ->allowedFilters([
            AllowedFilter::exact('tags'),
            AllowedFilter::exact('rating'),
        ]);

    $request = [
        'facet' => ['tags', 'rating'],
        'filter' => ['tags' => 'water'],
    ];

    facetQuery($spec, $request)
        ->assertBucketKeys('tags', ['water', 'drinks', 'clothes', 'gloves', 'video'])
        ->assertMinMax('rating', 8, 10);
});

test('facets in nested specification', function () {
    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec
                ->allowedFilters([
                    AllowedFilter::exact('price'),
                    AllowedFilter::exact('seller_id'),
                ])
                ->allowedFacets([
                    AllowedFacet::minmax('price'),
                    AllowedFacet::terms('seller_id'),
                ]);
        });

    $request = [
        'facet' => ['price', 'seller_id'],
        'filter' => ['seller_id' => 20],
    ];

    facetQuery($spec, $request)
        ->assertBucketKeys('seller_id', [10, 15, 20, 90])
        ->assertMinMax('price', 200, 28990);
});

test('facets in different nested specification', function () {
    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec->allowedFilters([AllowedFilter::exact('price')])
                ->allowedFacets([AllowedFacet::minmax('price')]);
        })
        ->nested('offers', function (Specification $spec) {
            $spec->allowedFilters([AllowedFilter::exact('seller_id')])
                ->allowedFacets([AllowedFacet::terms('seller_id')]);
        });

    $request = [
        'facet' => ['price', 'seller_id'],
        'filter' => ['seller_id' => 90],
    ];

    facetQuery($spec, $request)
        ->assertBucketKeys('seller_id', [10, 15, 20, 90])
        ->assertMinMax('price', 980, 41999);
});

test('facets in root and nested specifications', function () {
    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec->allowedFilters([AllowedFilter::exact('seller_id')])
                ->allowedFacets([AllowedFacet::terms('seller_id')]);
        })
        ->allowedFacets([AllowedFacet::terms('tags')])
        ->allowedFilters([AllowedFilter::exact('tags')]);

    $request = [
        'facet' => ['tags', 'seller_id'],
        'filter' => ['tags' => 'water', 'seller_id' => 20],
    ];

    facetQuery($spec, $request)
        ->assertBucketKeys('tags', ['water', 'drinks', 'video'])
        ->assertBucketKeys('seller_id', [10, 15, 20]);
});

test('facet with multiple filters', function () {
    $spec = CompositeSpecification::new()
        ->nested('offers', function (Specification $spec) {
            $spec
                ->allowedFilters([
                    AllowedFilter::greaterOrEqual('price__gte', 'price'),
                    AllowedFilter::lessOrEqual('price__lte', 'price'),
                    AllowedFilter::exact('seller_id'),
                ])
                ->allowedFacets([
                    AllowedFacet::minmax('price', ['price__gte', 'price__lte']),
                    AllowedFacet::terms('seller_id'),
                ]);
        });

    $request = [
        'facet' => ['price', 'seller_id'],
        'filter' => ['seller_id' => 20, 'price__gte' => 300, 'price__lte' => 20000],
    ];

    facetQuery($spec, $request)
        ->assertBucketKeys('seller_id', [10, 15, 90])
        ->assertMinMax('price', 200, 28990);
});
