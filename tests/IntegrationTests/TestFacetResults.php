<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\IntegrationTests;

use Ensi\LaravelElasticQuery\Aggregating\AggregationsQuery;
use Ensi\LaravelElasticQuerySpecification\Contracts\QueryParameters;
use Ensi\LaravelElasticQuerySpecification\FacetQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Tests\Data\Models\ProductsIndex;

class TestFacetResults extends TestAggregationResults
{
    public static function make(
        CompositeSpecification $spec,
        QueryParameters $parameters,
        ?AggregationsQuery $query = null
    ): self {
        $builder = new FacetQueryBuilder($query ?? ProductsIndex::aggregate(), $spec, $parameters);
        $builder->validateResolved();

        return new self($builder->get());
    }
}
