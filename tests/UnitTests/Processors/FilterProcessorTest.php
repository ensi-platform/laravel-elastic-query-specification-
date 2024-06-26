<?php

use Ensi\LaravelElasticQuerySpecification\Exceptions\InvalidQueryException;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Processors\FilterProcessor;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors\FluentProcessor;

uses(UnitTestCase::class);

test('not allowed filters', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->allowedFilters(['foo', 'bar']);

    $processor = new FilterProcessor(collect(['foo' => 1, 'baz' => 2]));
    $processor->visitRoot($spec);

    expect(fn () => $processor->done())
        ->toThrow(InvalidQueryException::class);
});

test('set filter value', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo');
    $spec = Specification::new()->allowedFilters([$filter]);

    FluentProcessor::new(FilterProcessor::class, ['foo' => 1])
        ->visitRoot($spec)
        ->done();

    expect($filter->value())->toBe(1);
});

test('ignore missing values', function () {
    /** @var UnitTestCase $this */

    $filter = AllowedFilter::exact('foo');
    $spec = Specification::new()->allowedFilters([$filter]);

    FluentProcessor::new(FilterProcessor::class, [])
        ->visitRoot($spec)
        ->done();

    expect($filter->value())->toBeNull();
});
