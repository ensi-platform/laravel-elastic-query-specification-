<?php

use Ensi\LaravelElasticQuerySpecification\Filtering\FilterValue;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('when', function () {
    /** @var UnitTestCase $this */

    FilterValue::make('foo')
        ->when(true, expectInvoke($this->mockCallable(), 1, 'foo'));
});

test('when multiple', function () {
    /** @var UnitTestCase $this */

    FilterValue::make(['foo', 'bar'])
        ->whenMultiple(expectInvoke($this->mockCallable(), 1, ['foo', 'bar']));
});

test('when single', function (mixed $source, mixed $expected) {
    /** @var UnitTestCase $this */

    FilterValue::make($source)
        ->whenSingle(expectInvoke($this->mockCallable(), 1, $expected));
})->with([
    'string' => ['foo', 'foo'],
    'array with one element' => [['foo'], 'foo'],
    'array with one not null element' => [[null, 'foo', null], 'foo'],
]);

test('when same', function (mixed $value) {
    /** @var UnitTestCase $this */

    FilterValue::make($value)
        ->whenSame($value, expectInvoke($this->mockCallable(), 1, $value));
})->with([
    'boolean' => [true],
    'integer' => [120],
    'string' => ['foo'],
]);

test('else', function () {
    /** @var UnitTestCase $this */

    FilterValue::make('foo')
        ->whenMultiple(expectInvoke($this->mockCallable(), 0))
        ->orElse(expectInvoke($this->mockCallable(), 1, 'foo'));
});

test('call only first callback', function () {
    /** @var UnitTestCase $this */

    FilterValue::make('foo')
        ->whenMultiple(expectInvoke($this->mockCallable(), 0))
        ->whenSame('foo', expectInvoke($this->mockCallable(), 1))
        ->whenSingle(expectInvoke($this->mockCallable(), 0))
        ->orElse(expectInvoke($this->mockCallable(), 0));
});
