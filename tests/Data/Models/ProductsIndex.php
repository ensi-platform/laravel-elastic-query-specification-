<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\Data\Models;

use Ensi\LaravelElasticQuery\ElasticIndex;

class ProductsIndex extends ElasticIndex
{
    protected string $name = 'test_spec_products';

    protected string $tiebreaker = 'product_id';
}
