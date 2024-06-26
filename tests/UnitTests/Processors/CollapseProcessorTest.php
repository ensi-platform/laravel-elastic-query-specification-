<?php

use Ensi\LaravelElasticQuery\Contracts\CollapsibleQuery;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Processors\CollapseProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('collapses', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedCollapses(['foo', 'bar', 'baz']);

    $query = $this->mock(CollapsibleQuery::class);
    $query->expects('collapse')
        ->times(1)
        ->andReturnSelf();

    FluentProcessor::new(CollapseProcessor::class, $query, 'bar')
        ->visitRoot($spec)
        ->done();
});

test('not allowed collapse', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedCollapses(['foo']);

    $query = $this->mock(CollapsibleQuery::class);
    $query->expects('collapse')->andReturnSelf()->never();

    FluentProcessor::new(CollapseProcessor::class, $query, 'bar')
        ->visitRoot($spec)
        ->done();
})->throws(InvalidQueryException::class);
