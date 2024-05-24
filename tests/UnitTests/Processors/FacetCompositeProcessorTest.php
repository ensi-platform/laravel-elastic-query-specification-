<?php

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Processors\FacetCompositeProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Specification\Visitor;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Illuminate\Support\Collection;

uses(UnitTestCase::class);

test('no active facets', function () {
    /** @var UnitTestCase $this */

    $visitor = $this->mock(Visitor::class);
    $visitor->expects('visitRoot')->never();
    $visitor->expects('visitNested')->never();

    $processor = new FacetCompositeProcessor(
        $this->mock(AggregationsQuery::class),
        new Collection(),
        fn () => $visitor
    );

    $processor->visitRoot(Specification::new());
    $processor->visitNested('foo', Specification::new());
    $processor->done();
});

test('root specification contains active facet', function () {
    /** @var UnitTestCase $this */

    $query = $this->mock(AggregationsQuery::class);
    $query->allows('composite')
        ->andReturnUsing(function (Closure $callback) use ($query) {
            $callback($query);

            return $query;
        });

    $visitor = $this->mock(Visitor::class);
    $visitor->expects('visitRoot')->once();
    $visitor->expects('done')->once();

    $processor = new FacetCompositeProcessor(
        $query,
        new Collection(['foo']),
        fn () => $visitor
    );

    $facet = AllowedFacet::minmax('foo');
    $facet->enable();

    $processor->visitRoot(Specification::new()->allowedFacets([$facet]));
    $processor->done();
});

test('nested specification contains active facet', function () {
    /** @var UnitTestCase $this */

    $query = $this->mock(AggregationsQuery::class);
    $query->allows('composite')
        ->andReturnUsing(function (Closure $callback) use ($query) {
            $callback($query);

            return $query;
        });

    $visitor = $this->mock(Visitor::class);
    $visitor->expects('visitNested')->once();
    $visitor->expects('done')->once();

    $processor = new FacetCompositeProcessor(
        $query,
        new Collection(['foo']),
        fn () => $visitor
    );

    $facet = AllowedFacet::minmax('foo');
    $facet->enable();

    $processor->visitNested('bar', Specification::new()->allowedFacets([$facet]));
    $processor->done();
});
