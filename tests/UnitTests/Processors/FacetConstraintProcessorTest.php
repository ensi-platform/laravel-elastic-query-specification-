<?php

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Processors\FacetConstraintProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('skip active facet filters in root specification', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo')->setValue(10);
    $facet = AllowedFacet::minmax('foo');
    $facet->attachFilter($filter);
    $facet->enable();

    $spec = Specification::new()
        ->allowedFilters([$filter])
        ->allowedFacets([$facet]);

    $query = $this->mock(BoolQuery::class);
    $query->expects('where')
        ->with('foo', 10)
        ->never();

    FluentProcessor::new(FacetConstraintProcessor::class, $query)
        ->visitRoot($spec);
});

test('root specification has inactive facet filter', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo')->setValue(10);
    $facet = AllowedFacet::minmax('foo');
    $facet->attachFilter($filter);

    $spec = Specification::new()
        ->allowedFilters([$filter])
        ->allowedFacets([$facet]);

    $query = $this->mock(BoolQuery::class);
    $query->expects('where')
        ->with('foo', 10)
        ->once()
        ->andReturnSelf();

    FluentProcessor::new(FacetConstraintProcessor::class, $query)
        ->visitRoot($spec);
});

test('nested specification has active facet', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::minmax('foo');
    $facet->enable();

    $spec = Specification::new()->allowedFacets([$facet]);

    $query = $this->mock(BoolQuery::class);
    $query->expects('whereHas')->andReturnSelf()->never();

    FluentProcessor::new(FacetConstraintProcessor::class, $query)
        ->visitNested('nested', $spec);
});
