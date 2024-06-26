<?php

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Processors\ConstraintProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('visit root', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->where('foo', 10);

    $query = $this->mock(BoolQuery::class);
    $query->expects('where')
        ->with('foo', 10)
        ->once()
        ->andReturnSelf();

    FluentProcessor::new(ConstraintProcessor::class, $query)
        ->visitRoot($spec);
});

test('nested constraint', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedFilters([
        AllowedFilter::exact('foo')->default(10),
    ]);

    $query = $this->mock(BoolQuery::class);
    $query->expects('whereHas')
        ->with('nested', any())
        ->once()
        ->andReturnSelf();

    FluentProcessor::new(ConstraintProcessor::class, $query)
        ->visitNested('nested', $spec);
});

test('nested no active filters', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->where('foo', 10);

    $query = $this->mock(BoolQuery::class);
    $query->expects('whereHas')->andReturnSelf()->never();

    FluentProcessor::new(ConstraintProcessor::class, $query)
        ->visitNested('nested', $spec);
});
