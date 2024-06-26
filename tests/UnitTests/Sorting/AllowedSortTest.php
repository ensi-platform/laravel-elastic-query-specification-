<?php

use Ensi\LaravelElasticQuery\Contracts\MissingValuesMode;
use Ensi\LaravelElasticQuery\Contracts\SortableQuery;
use Ensi\LaravelElasticQuery\Contracts\SortMode;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuerySpecification\Contracts\SortAction;
use Ensi\LaravelElasticQuerySpecification\Sorting\AllowedSort;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;

uses(UnitTestCase::class);

test('parse name', function (string $source, string $expected) {
    /** @var UnitTestCase $this */

    [$name] = AllowedSort::parseNameAndOrder($source);

    expect($name)->toBe($expected);
})->with([
    'without order' => ['foo', 'foo'],
    'with order' => ['-foo', 'foo'],
]);

test('parse order', function (string $source, ?string $expected) {
    /** @var UnitTestCase $this */

    [, $order] = AllowedSort::parseNameAndOrder($source);

    expect($order)->toBe($expected);
})->with([
    'without order' => ['foo', null],
    'ascending' => ['+foo', SortOrder::ASC],
    'descending' => ['-foo', SortOrder::DESC],
]);

test('construct sets default order', function () {
    /** @var UnitTestCase $this */

    $action = expectInvoke($this->mock(SortAction::class), 1, any(), SortOrder::DESC, any(), 'foo', any());

    $allowedSort = AllowedSort::custom('-foo', $action);
    $allowedSort($this->mock(SortableQuery::class), null);
});

test('construct sets field', function (string $name, ?string $field, string $expected) {
    /** @var UnitTestCase $this */

    $action = expectInvoke($this->mock(SortAction::class), 1, any(), any(), any(), $expected, any());

    $allowedSort = AllowedSort::custom($name, $action, $field);
    $allowedSort($this->mock(SortableQuery::class), null);
})->with([
    'only name' => ['foo', null, 'foo'],
    'name and field' => ['foo', 'bar', 'bar'],
]);

test('mode', function () {
    /** @var UnitTestCase $this */

    $action = expectInvoke($this->mock(SortAction::class), 1, any(), any(), SortMode::MEDIAN, any(), any());

    $allowedSort = AllowedSort::custom('-foo', $action)->mode(SortMode::MEDIAN);
    $allowedSort($this->mock(SortableQuery::class), null);
});

test('missing values', function () {
    /** @var UnitTestCase $this */

    $action = expectInvoke($this->mock(SortAction::class), 1, any(), any(), any(), any(), MissingValuesMode::FIRST);

    $allowedSort = AllowedSort::custom('-foo', $action)->missingValuesFirst();
    $allowedSort($this->mock(SortableQuery::class), null);
});
