<?php

use Ensi\LaravelElasticQuery\Contracts\AggregationsBuilder;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Processors\FacetQueryProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('process root specification with current facet', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::terms('foo');
    $facet->attachFilter(AllowedFilter::exact('foo')->default(10));
    $facet->enable();

    $spec = Specification::new()->allowedFacets([$facet]);

    $query = $this->mock(AggregationsBuilder::class);
    $query->expects('terms')->once();
    $query->expects('where')->never();

    FluentProcessor::new(FacetQueryProcessor::class, $query, 'foo')
        ->visitRoot($spec)
        ->done();
});

test('process root specification without current facet', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::terms('foo');
    $facet->attachFilter(AllowedFilter::exact('foo')->default(10));
    $facet->enable();

    $spec = Specification::new()->allowedFacets([$facet]);

    $query = $this->mock(AggregationsBuilder::class);
    $query->expects('terms')->never();
    $query->expects('where')->with('foo', 10)->once();

    FluentProcessor::new(FacetQueryProcessor::class, $query, 'bar')
        ->visitRoot($spec)
        ->done();
});

test('process nested specification with current facet', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo')->default(10);
    $facet = AllowedFacet::terms('foo');
    $facet->attachFilter($filter);
    $facet->enable();

    $spec = Specification::new()
        ->allowedFacets([$facet])
        ->allowedFilters([$filter]);

    $query = $this->mock(AggregationsBuilder::class);
    $query->allows('nested')
        ->with('field', any())
        ->andReturnUsing(function ($field, callable $callback) use ($query) {
            $callback($query);

            return $query;
        });

    $query->expects('terms')->once();
    $query->expects('where')->never();

    FluentProcessor::new(FacetQueryProcessor::class, $query, 'foo')
        ->visitNested('field', $spec)
        ->done();
});

test('process nested specification without current facet', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo')->default(10);
    $facet = AllowedFacet::terms('foo');
    $facet->attachFilter($filter);
    $facet->enable();

    $spec = Specification::new()
        ->allowedFacets([$facet])
        ->allowedFilters([$filter])
        ->where('bar', 50);

    $query = $this->mock(AggregationsBuilder::class);
    $query->allows('whereHas')
        ->with('field', any())
        ->andReturnUsing(function ($field, callable $callback) use ($query) {
            $callback($query);

            return $query;
        });

    $query->expects('terms')->never();
    $query->expects('where')->with('foo', 10)->once();
    $query->expects('where')->with('bar', 50)->once();

    FluentProcessor::new(FacetQueryProcessor::class, $query, 'bar')
        ->visitNested('field', $spec)
        ->done();
});
