<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuery\Aggregating\MinMax;
use Ensi\LaravelElasticQuerySpecification\AggregateQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\Contracts\QueryParameters;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Tests\Data\Models\ProductsIndex;
use Illuminate\Support\Collection;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;

class TestAggregationResults
{
    public function __construct(protected Collection $results)
    {
    }

    public static function make(
        CompositeSpecification $spec,
        QueryParameters $parameters,
        ?AggregationsQuery $query = null
    ): self {
        $builder = new AggregateQueryBuilder($query ?? ProductsIndex::aggregate(), $spec, $parameters);
        $builder->validateResolved();

        return new self($builder->get());
    }

    public function all(): Collection
    {
        return $this->results;
    }

    public function get(string $key): mixed
    {
        return $this->results->get($key);
    }

    public function assertBucketKeys(string $aggName, array $expected): self
    {
        assertEqualsCanonicalizing(
            $expected,
            $this->get($aggName)?->pluck('key')?->all()
        );

        return $this;
    }

    public function assertBucketKeysCount(string $aggName, int $expected): self
    {
        assertCount($expected, $this->get($aggName));

        return $this;
    }

    public function assertMinMax(string $aggName, mixed $min, mixed $max): self
    {
        assertEquals(new MinMax($min, $max), $this->get($aggName));

        return $this;
    }

    public function assertValue(string $aggName, mixed $expected): self
    {
        assertEquals($expected, $this->get($aggName));

        return $this;
    }

    public function dump(): static
    {
        $this->results->dump();

        return $this;
    }
}
