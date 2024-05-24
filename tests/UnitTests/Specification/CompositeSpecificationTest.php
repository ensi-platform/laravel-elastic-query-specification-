<?php

use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Specification\Stubs\VisitorStub;

uses(UnitTestCase::class);

test('forward calls returns self', function () {
    /** @var UnitTestCase $this */

    $spec = new CompositeSpecification();

    expect($spec->allowedFilters(['foo']))->toBe($spec);
});

test('add nested', function () {
    /** @var UnitTestCase $this */

    $spec = CompositeSpecification::new()->nested('foo', fn () => null);

    expect(VisitorStub::inspect($spec)->nestedFields)->toEqual(['foo']);
});

test('add nested instance', function () {
    /** @var UnitTestCase $this */

    $nested = Specification::new();
    $spec = CompositeSpecification::new()->nested('foo', $nested);

    expect(VisitorStub::inspect($spec)->nestedSpecifications)
        ->toHaveCount(1)
        ->each->toBe($nested);
});

test('accept visit root', function () {
    /** @var UnitTestCase $this */

    $spec = new CompositeSpecification();

    expect(VisitorStub::inspect($spec)->rootSpecification)->not->toBeNull();
});

test('accept visit nested', function () {
    /** @var UnitTestCase $this */

    $spec = CompositeSpecification::new()->nested('foo', fn () => null);

    expect(VisitorStub::inspect($spec)->nestedSpecifications)->toHaveCount(1);
});

test('accept done', function () {
    /** @var UnitTestCase $this */

    $spec = new CompositeSpecification();

    expect(VisitorStub::inspect($spec)->done)->toBeTrue();
});
