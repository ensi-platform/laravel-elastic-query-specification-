<?php

use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Concerns\Stubs\ExtractsQueryParametersStub;

use function PHPUnit\Framework\assertEquals;

uses(UnitTestCase::class);

test('convert filter values', function (mixed $value, mixed $expected) {
    /** @var UnitTestCase $this */
    $parameters = new ExtractsQueryParametersStub([
        'filter' => ['name' => $value],
    ]);

    assertEquals(['name' => $expected], $parameters->filters()->all());
})->with([
    'true' => ['true', true],
    'false' => ['false', false],
    'array of boolean' => [['true', 'false'], [true, false]],
    'assoc array' => [['foo' => 'bar', 'baz' => 'true'], ['foo' => 'bar', 'baz' => true]],
]);

test('sorts', function (mixed $value, array $expected) {
    /** @var UnitTestCase $this */
    $parameters = new ExtractsQueryParametersStub(['sort' => $value]);

    assertEquals($expected, $parameters->sorts()->all());
})->with([
    'array' => [['foo', '-bar'], ['foo', '-bar']],
    'string' => ['-foo,+bar, baz', ['-foo', '+bar', 'baz']],
    'with empty' => ['foo,,bar', ['foo', 'bar']],
]);
