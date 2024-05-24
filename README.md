# Laravel Elastic Query Specification

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ensi/laravel-elastic-query-specification.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-elastic-query-specification)
[![Tests](https://github.com/ensi-platform/laravel-elastic-query-specification/actions/workflows/run-tests.yml/badge.svg?branch=v8)](https://github.com/ensi-platform/laravel-elastic-query-specification/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ensi/laravel-elastic-query-specification.svg?style=flat-square)](https://packagist.org/packages/ensi/laravel-elastic-query-specification)

Extension for [ensi/laravel-elastic-query](https://github.com/ensi-platform/laravel-elastic-query/) to describe queries in a declarative way.

## Installation

1. Install [ensi/laravel-elastic-query](https://github.com/ensi-platform/laravel-elastic-query/) https://github.com/ensi-platform/laravel-elastic-query#installation
2. Install this package via composer:

```bash
composer require ensi/laravel-elastic-query-specification
```

## Version Compatibility

| Laravel Elastic Query Specification                                                                | Laravel                              | PHP  | Laravel Elastic Query |
|----------------------------------------------------------------------------------------------------|--------------------------------------|------|-----------------------|
| ^0.1.0                                                                                             | ^8.0                                 | ^8.0 | ^0.2.0                |
| ^0.2.0                                                                                             | ^8.0                                 | ^8.0 | ^0.3.0                |
| ^0.2.3                                                                                             | ^8.0 \|\| ^9.0                       | ^8.0 | ^0.3.0                |
| ^0.3.0                                                                                             | ^8.0 \|\| ^9.0                       | ^8.0 | ^0.3.0                |
| ^7.x ([see details](https://github.com/ensi-platform/laravel-elastic-query-specification/tree/v7)) | ^9.0 \|\| ^10.0 \|\| ^11.0           | ^8.1 | ^7.1.0                |
| ^8.0.0                                                                                             | ^8.0 \|\| ^9.0                       | ^8.0 | ^8.0                  |
| ^8.0.2                                                                                             | ^8.0 \|\| ^9.0 \|\| ^10.0            | ^8.0 | ^8.0                  |
| ^8.0.3                                                                                             | ^8.0 \|\| ^9.0 \|\| ^10.0 \|\| ^11.0 | ^8.0 | ^8.0.23               |
| ^8.1.0                                                                                             | ^9.0 \|\| ^10.0 \|\| ^11.0           | ^8.1 | ^8.1.0                |

## Basic usage

All types of declarative queries are based on the specification. It contains definitions of available filters, sorts, and aggregates.

```php
use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Sorting\AllowedSort;
use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;

class ProductSpecification extends CompositeSpecification
{
    public function __construct()
    {
        parent::__construct();
        
        $this->allowedFilters([
            'package',
            'active',
            AllowedFilter::exact('cashback', 'cashback.active')->default(true)
        ]);
        
        $this->allowedSorts(['name', 'rating']);
        
        $this->allowedAggregates([
            'package',
            AllowedAggregate::minmax('rating')
        ]);
        
        $this->allowedFacets([
            'package'
        ]);
        
        $this->whereNotNull('package');
        
        $this->nested('offers', function (Specification $spec) {
            $spec->allowedFilters(['seller_id', 'active']);
            
            $spec->allowedAggregates([
                'seller_id',
                AllowedAggregate::minmax('price')
            ]);
            
            $spec->allowedSorts([
                AllowedSort::field('price')->byMin()
            ]);
            
            $spec->allowedFacets([
                AllowedFacet::terms('seller_id')
            ]);
        });
    }
}
```

Here are examples of queries for this specification.
```json
{
 "sort": ["+price", "-rating"],
 "filter": {
    "active": true,
    "seller_id": 10
 }
}
```
```json
{
 "aggregate": ["price", "rating"],
 "filter": {
    "package": "bottle",
    "seller_id": 10
 }
}
```
```json
{
  "facet": ["seller_id", "package"],
  "filter": {
    "package": "bottle",
    "seller_id": [10, 20, 50, 90]
  }
}
```

The `nested` method adds specifications for nested documents. The names of filters, aggregates, and sorts are exported
from them to the global scope without adding any prefixes. It is acceptable to have the same names for filters, but not
for other components.

```php
$this->nested('nested_field', function (Specification $spec) { ... })
$this->nested('nested_field', new SomeSpecificationImpl());
```

In the specifications for nested documents, only the fields of these documents can be used.

It is acceptable to add several specifications for the same `nested` field.

The `where*` constraints allow you to set additional program selection conditions that cannot be changed by the client.
The constraints specified in the root specification are always applied. Constraints in the nested specifications are
only used as additions to filters, aggregates, or sorts added to the query. For example, if there is no active filter
in the nested specification, then the constraints from this specification will not fall into the filters section
of the Elasticsearch query.

The `allowedFilters` method determines the filters available to the client. Each filter must contain a unique name within
the specification. At the same time, in the root and nested specifications or in different nested specifications,
the names may be repeated. All filters with the same name will be filled with one value from the query parameters.

In addition to the name of the filter itself, you can separately specify the name of the field in the index for which
it is applied, and the default value.

```php
$this->allowedFilters([AllowedFilter::exact('name', 'field')->default(500)]);

// the following statements are equivalent
$this->allowedFilters(['name']);
$this->allowedFilters([AllowedFilter::exact('name', 'name')]);
```

Types of filters

```php
AllowedFilter::exact('name', 'field');          // The field value is checked for equality to one of the specified
AllowedFilter::exists('name', 'field');         // There is a check that the field is in the document and has a non-zero value.
AllowedFilter::greater('name', 'field');        // The field value must be greater than the specified one.
AllowedFilter::greaterOrEqual('name', 'field'); // The field value must be greater than or equal to the specified one.
AllowedFilter::less('name', 'field');           // The field value must be less than the specified one.
AllowedFilter::lessOrEqual('name', 'field');    // The field value must be less than or equal to the specified one.
AllowedFilter::match('name', 'field');          // Full text search in the field
AllowedFilter::multiMatch('name', ['field1^3', 'field2']);    // Full text search in the fields
```

The sorts available to the client are added by the `allowedSorts` method. The sorting direction is set in its name.
The sign `+` or the absence of a sign corresponds to the ascending order, `-` to the descending order.
By default, ascending sorting is used with the minimum selection, if there are several values in the field.

```php
$this->allowedSorts([AllowedSort::field('name', 'field')]);

// the following statements are equivalent
$this->allowedSorts(['name']);
$this->allowedSorts([AllowedSort::field('+name', 'name')]);
$this->allowedSorts([AllowedSort::field('+name', 'name')->byMin()]);

// set the sorting mode
$this->allowedSorts([AllowedSort::field('name', 'field')->byMin()]);
$this->allowedSorts([AllowedSort::field('name', 'field')->byMax()]);
$this->allowedSorts([AllowedSort::field('name', 'field')->byAvg()]);
$this->allowedSorts([AllowedSort::field('name', 'field')->bySum()]);
$this->allowedSorts([AllowedSort::field('name', 'field')->byMedian()]);
```

To sort from a nested specification, all constraints and active filters from the same specification are taken into account.

Aggregates are declared with the `allowedAggregates` method. The client specifies in the query parameters a list of names
of aggregates, the results of which he expects in the response.

```php
$this->allowedAggregates([AllowedAggregate::terms('name', 'field')]);

// the following statements are equivalent
$this->allowedAggregates(['name']);
$this->allowedAggregates([AllowedAggregate::terms('name', 'name')]);
```

Types of aggregates

```php
AllowedAggregate::terms('name', 'field');   // Get all variants of attribute values
AllowedAggregate::minmax('name', 'field');  // Get min and max attribute values
```

Aggregates from nested specifications are added to the Elasticsearch query with all constraints and active filters.

You can use the `allowedFacets` method to define facets. Each facet requires an aggregate and one or
more filters. You can use both the existing aggregate

```php
AllowedFacet::fromAggregate('name', 'filter');
```

and the aggregate created by the facet itself

```php
AllowedFacet::terms('name', 'filter');
AllowedFacet::minmax('name', ['filter1', 'filter2']);
```

Filters are registered in the specification separately. Only their names are passed to facet creation methods.

During the calculation of the available values for each facet, all set filters are applied except those associated with
this facet.

## Search for documents

```php
use Ensi\LaravelElasticQuerySpecification\SearchQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\QueryBuilderRequest;

class ProductsSearchQuery extends SearchQueryBuilder
{
    public function __construct(QueryBuilderRequest $request)
    {
        parent::__construct(ProductsIndex::query(), new ProductSpecification(), $request);
    }
}
```

```php
class ProductsController
{
    // ...
    public function index(ProductsSearchQuery $query)
    {
        return ProductResource::collection($query->get());
    }
}
```

## Calculation of summary indicators

```php
use Ensi\LaravelElasticQuerySpecification\AggregateQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\QueryBuilderRequest;

class ProductsAggregateQuery extends AggregateQueryBuilder
{
    public function __construct(QueryBuilderRequest $request)
    {
        parent::__construct(ProductsIndex::aggregate(), new ProductSpecification(), $request);
    }
}
```

```php
class ProductsController
{
    // ...
    public function totals(ProductsAggregateQuery $query)
    {
        return new ProductAggregateResource($query->get());
    }
}
```

## Determining the available facet values

```php
use Ensi\LaravelElasticQuerySpecification\FacetQueryBuilder;
use Ensi\LaravelElasticQuerySpecification\QueryBuilderRequest;

class ProductsFacetsQuery extends FacetQueryBuilder
{
    public function __construct(QueryBuilderRequest $request)
    {
        parent::__construct(ProductsIndex::aggregate(), new ProductSpecification(), $request);
    }
}
```

```php
class ProductsController
{
    // ...
    public function facets(ProductsFacetsQuery $query)
    {
        return new ProductFacetsResource($query->get());
    }
}
```

## Elasticsearch 7 and 8 support.

Due to the incompatibility of clients for Elasticsearch 7 and 8, separate releases will be created for these versions.
Development for each version is carried out in the corresponding branch.

To make changes to version 7, you need to create a task branch based on v7 and make a pull request to it.
For version 8 it is similar, but based on the v8 branch.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. start Elasticsearch in your preferred way
3. if you need change `ELASTICSEARCH_HOSTS`, copy `phpunit.xml.dist` to `phpunit.xml` and fill value
4. composer test

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
