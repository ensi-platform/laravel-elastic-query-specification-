<?php

use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Collapsing\AllowedCollapse;
use Ensi\LaravelElasticQuerySpecification\Contracts\Constraint;
use Ensi\LaravelElasticQuerySpecification\Exceptions\ComponentExistsException;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Sorting\AllowedSort;
use Ensi\LaravelElasticQuerySpecification\Specification\CallbackConstraint;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('allowed filters', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedFilters(['foo', AllowedFilter::exact('bar')]);

    expect($spec->filters())->toHaveCount(2);
});

test('add custom constraint', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->addConstraint($this->mock(Constraint::class));

    expect($spec->constraints())->toHaveCount(1);
});

test('add callback constraint', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()->addConstraint(fn () => null);

    expect($spec->constraints())
        ->each()
        ->toBeInstanceOf(CallbackConstraint::class);
});

test('constraints includes filters', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedFilters(['foo'])
        ->addConstraint(fn () => null);

    expect($spec->constraints())->toHaveCount(2);
});

test('allowed sorts', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedSorts(['foo', AllowedSort::field('bar')]);

    expect($spec->sorts())->toHaveCount(2);
});

test('allowed aggregates', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedAggregates(['foo', AllowedAggregate::terms('bar')]);

    expect($spec->aggregates())->toHaveCount(2);
});

test('allowed facets', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedFacets(['foo', AllowedFacet::terms('bar')]);

    expect($spec->facets())->toHaveCount(2);
});

test('allowed collapses', function () {
    /** @var UnitTestCase $this */

    $spec = Specification::new()
        ->allowedCollapses(['foo', AllowedCollapse::field('bar')]);

    expect($spec->collapses())->toHaveCount(2);
});

test('duplicate component name', function (string $method) {
    /** @var UnitTestCase $this */

    expect(fn () => Specification::new()->{$method}(['foo', 'bar', 'foo']))
        ->toThrow(ComponentExistsException::class);
})->with([
    'filter' => ['allowedFilters'],
    'sort' => ['allowedSorts'],
    'aggregate' => ['allowedAggregates'],
]);
