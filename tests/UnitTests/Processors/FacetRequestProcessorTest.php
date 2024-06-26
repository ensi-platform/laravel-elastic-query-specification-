<?php

use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Exceptions\AggregateNotFoundException;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Exceptions\NotUniqueNameException;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Processors\FacetRequestProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;
use Illuminate\Support\Collection;

uses(UnitTestCase::class);

test('not allowed facets', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedFilters([AllowedFilter::exact('foo')])
        ->allowedFacets([AllowedFacet::terms('foo')]);

    $processor = new FacetRequestProcessor(new Collection(['foo', 'bar']));
    $processor->visitRoot($spec);

    expect(fn () => $processor->done())
        ->toThrow(InvalidQueryException::class);
});

test('attach aggregate', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::fromAggregate('foo');
    $agg = AllowedAggregate::minmax('foo');

    $spec = Specification::new()
        ->allowedFacets([$facet])
        ->allowedAggregates([$agg])
        ->allowedFilters([AllowedFilter::exact('foo')]);

    FluentProcessor::new(FacetRequestProcessor::class, [])
        ->visitRoot($spec)
        ->done();

    expect($facet->aggregate())->toBe($agg);
});

test('missing aggregate', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedFacets(['foo']);
    $processor = new FacetRequestProcessor(new Collection());

    expect(fn () => $processor->visitRoot($spec))
        ->toThrow(AggregateNotFoundException::class);
});

test('enable requested facet', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::terms('foo');
    $spec = Specification::new()
        ->allowedFilters([AllowedFilter::exact('foo')])
        ->allowedFacets([$facet]);

    FluentProcessor::new(FacetRequestProcessor::class, ['foo'])
        ->visitRoot($spec)
        ->done();

    expect($facet->isActive())->toBeTrue();
});

test('disable not requested facet', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::terms('foo');
    $spec = Specification::new()
        ->allowedFilters([AllowedFilter::exact('foo')])
        ->allowedFacets([$facet]);

    FluentProcessor::new(FacetRequestProcessor::class, [])
        ->visitRoot($spec)
        ->done();

    expect($facet->isActive())->toBeFalse();
});

test('not unique name', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedFilters([AllowedFilter::exact('foo')])
        ->allowedFacets([AllowedFacet::terms('foo')]);

    $processor = new FacetRequestProcessor(new Collection([]));
    $processor->visitRoot($spec);

    expect(fn () => $processor->visitNested('bar', $spec))
        ->toThrow(NotUniqueNameException::class);
});

test('attach filter', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::terms('foo');
    $filter = AllowedFilter::exact('foo');
    $spec = Specification::new()
        ->allowedFilters([$filter])
        ->allowedFacets([$facet]);

    $processor = new FacetRequestProcessor(new Collection([]));
    $processor->visitRoot($spec);

    expect($facet->filters())->toHaveCount(1)
        ->and($facet->filters()[0])->toBe($filter);
});
