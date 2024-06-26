<?php

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuerySpecification\CustomParameters;
use Ensi\LaravelElasticQuerySpecification\QueryBuilderRequest;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests\TestAggregationResults;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests\TestFacetResults;
use Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests\TestSearchResults;
use Ensi\LaravelElasticQuerySpecification\Tests\TestCase;
use Ensi\LaravelElasticQuerySpecification\Tests\UnitTestCase;
use Illuminate\Http\Request;
use Mockery\Matcher\Any;
use Mockery\MockInterface;

use function Pest\Laravel\instance;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses()->group('unit')->in('UnitTests');
uses()->group('integration')->in('IntegrationTests');

uses(UnitTestCase::class)->in(__DIR__ . '/Unit');
uses(TestCase::class)->in(__DIR__ . '/Integration');


/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

function any(): Any
{
    return Mockery::any();
}

/**
 * @template T
 * @class-string<T> string $className
 * @return T
 */
function expectInvoke(MockInterface $mock, int $times, mixed ...$parameters): MockInterface
{
    $expectation = $mock->expects('__invoke');

    if (count($parameters) > 0) {
        $expectation->with(...$parameters);
    }

    $expectation->times($times);

    return $mock;
}

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function searchQuery(CompositeSpecification $spec, array $parameters, ?SearchQuery $query = null): TestSearchResults
{
    $queryParameters = new CustomParameters($parameters);

    return TestSearchResults::make($spec, $queryParameters, $query);
}

function aggQuery(CompositeSpecification $spec, array $parameters, ?AggregationsQuery $query = null): TestAggregationResults
{
    $queryParameters = new CustomParameters($parameters);

    return TestAggregationResults::make($spec, $queryParameters, $query);
}

function facetQuery(CompositeSpecification $spec, array $parameters, ?AggregationsQuery $query = null): TestFacetResults
{
    $queryParameters = new CustomParameters($parameters);

    return TestFacetResults::make($spec, $queryParameters, $query);
}

function makeQueryRequest(array $input): QueryBuilderRequest
{
    $request = new Request($input);

    instance('request', $request);

    return resolve(QueryBuilderRequest::class);
}
