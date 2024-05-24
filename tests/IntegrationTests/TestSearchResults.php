<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests;

use Ensi\LaravelElasticQuery\Search\SearchQuery;
use Ensi\LaravelElasticQuerySpecification\Contracts\QueryParameters;
use Ensi\LaravelElasticQuerySpecification\SearchQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Tests\Data\Models\ProductsIndex;
use Illuminate\Support\Collection;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertEqualsCanonicalizing;

class TestSearchResults
{
    public function __construct(private Collection $results)
    {
    }

    public static function make(CompositeSpecification $spec, QueryParameters $parameters, ?SearchQuery $query = null): self
    {
        $builder = new SearchQueryBuilder($query ?? ProductsIndex::query(), $spec, $parameters);
        $builder->validateResolved();

        return new self($builder->get());
    }

    public function get(): Collection
    {
        return $this->results;
    }

    public function assertDocumentIds(array $expected): self
    {
        $actual = $this->get()
            ->pluck('_id')
            ->all();

        assertEqualsCanonicalizing($expected, $actual);

        return $this;
    }

    public function assertDocumentOrder(array $expected): self
    {
        $actual = $this->get()
            ->pluck('_id')
            ->all();

        assertEquals($expected, $actual);

        return $this;
    }
}
