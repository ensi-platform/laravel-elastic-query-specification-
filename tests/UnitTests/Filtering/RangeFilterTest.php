<?php

use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Filtering\RangeFilterAction;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('single value', function () {
    /** @var UnitTestCase $this */

    $query = $this->mock(BoolQuery::class);
    $query->expects('where')
        ->with('field', '>', 15)
        ->once()
        ->andReturnSelf();

    (new RangeFilterAction('>'))($query, 15, 'field');
});

test('multi value', function () {
    /** @var UnitTestCase $this */

    $query = $this->mock(BoolQuery::class);
    $query->expects('where')->never();

    (new RangeFilterAction('>'))($query, [15, 32], 'field');
})->throws(InvalidQueryException::class);
