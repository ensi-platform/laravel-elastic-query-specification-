<?php

use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('set name', function (string $method) {
    /** @var UnitTestCase $this */

    expect(AllowedFacet::$method('foo')->name())->toBe('foo');
})->with([
    'from aggregate' => ['fromAggregate'],
    'terms' => ['terms'],
    'minmax' => ['minmax'],
]);

test('set filter names', function ($names, array $expected) {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::fromAggregate('foo', $names);

    expect($facet->filterNames()->all())->toEqual($expected);
})->with([
    'default filter name' => [null, ['foo']],
    'single string filter name' => ['bar', ['bar']],
    'single item filter name' => [['bar'], ['bar']],
    'multiple filter names' => [['bar', 'baz'], ['bar', 'baz']],
]);

test('from existing aggregate', function () {
    /** @var UnitTestCase $this */

    expect(AllowedFacet::fromAggregate('foo')->aggregate())->toBeNull();
});

test('create internal aggregate', function (string $method) {
    /** @var UnitTestCase $this */

    expect(AllowedFacet::$method('foo')->aggregate())
        ->toBeInstanceOf(AllowedAggregate::class);
})->with([
    'terms' => ['terms'],
    'minmax' => ['minmax'],
]);

test('construct disabled', function () {
    /** @var UnitTestCase $this */

    expect(AllowedFacet::fromAggregate('foo')->isActive())->toBeFalse();
});

test('enable', function () {
    /** @var UnitTestCase $this */

    $facet = AllowedFacet::fromAggregate('foo');
    $facet->enable();

    expect($facet->isActive())->toBeTrue();
});
