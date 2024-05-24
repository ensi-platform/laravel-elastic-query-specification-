<?php

use Ensi\LaravelElasticQuery\Contracts\SortableQuery;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Exceptions\NotUniqueNameException;
use Ensi\LaravelElasticQuerySpecification\Processors\SortProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('root sorts', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedSorts(['foo', 'bar', 'baz']);

    $query = $this->mock(SortableQuery::class);
    $query->expects('sortBy')
        ->times(3)
        ->andReturnSelf();

    FluentProcessor::new(SortProcessor::class, $query, ['foo', '+bar', '-baz'])
        ->visitRoot($spec)
        ->done();
});

test('nested sorts', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedSorts(['foo']);

    $query = $this->mock(SortableQuery::class);
    $query->expects('sortByNested')
        ->with('nested', any())
        ->once()
        ->andReturnSelf();

    FluentProcessor::new(SortProcessor::class, $query, ['-foo'])
        ->visitNested('nested', $spec)
        ->done();
});

test('not allowed sorts', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedSorts(['foo']);

    $query = $this->mock(SortableQuery::class);
    $query->expects('sortBy')->andReturnSelf()->never();

    FluentProcessor::new(SortProcessor::class, $query, ['+bar'])
        ->visitRoot($spec)
        ->done();
})->throws(InvalidQueryException::class);

test('duplicate sort names', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedSorts(['foo']);
    $query = $this->mock(SortableQuery::class);

    FluentProcessor::new(SortProcessor::class, $query, ['foo'])
        ->visitRoot($spec)
        ->visitNested('nested', $spec);
})->throws(NotUniqueNameException::class);

test('pass order', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedSorts(['foo']);

    $query = $this->mock(SortableQuery::class);
    $query->expects('sortBy')
        ->with('foo', SortOrder::DESC, null, null)
        ->once()
        ->andReturnSelf();

    FluentProcessor::new(SortProcessor::class, $query, ['-foo'])
        ->visitRoot($spec)
        ->done();
});
