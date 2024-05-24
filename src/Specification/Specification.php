<?php

namespace Ensi\LaravelElasticQuerySpecification\Specification;

use Closure;
use Ensi\LaravelElasticQuery\Contracts\BoolQuery;
use Ensi\LaravelElasticQuerySpecification\Agregating\AllowedAggregate;
use Ensi\LaravelElasticQuerySpecification\Collapsing\AllowedCollapse;
use Ensi\LaravelElasticQuerySpecification\Contracts\Constraint;
use Ensi\LaravelElasticQuerySpecification\Exceptions\ComponentExistsException;
use Ensi\LaravelElasticQuerySpecification\Faceting\AllowedFacet;
use Ensi\LaravelElasticQuerySpecification\Filtering\AllowedFilter;
use Ensi\LaravelElasticQuerySpecification\Sorting\AllowedSort;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Specification
{
    /** @var array|AllowedFilter[] */
    private array $filters = [];

    /** @var array|AllowedSort[] */
    private array $sorts = [];

    /** @var array|Constraint[] */
    protected array $constraints = [];

    /** @var array|AllowedAggregate[] */
    protected array $aggregates = [];

    /** @var array|AllowedFacet[] */
    protected array $facets = [];

    /** @var array|AllowedCollapse[] */
    protected array $collapses = [];

    public static function new(): static
    {
        return new static();
    }

    //region Filters
    public function allowedFilters(array $filters): static
    {
        foreach ($filters as $filter) {
            $filter = AllowedFilter::wrap($filter);

            $this->addComponent($this->filters, $filter->name(), $filter, 'filter');

            $this->addConstraint($filter);
        }

        return $this;
    }

    /**
     * @return Collection|AllowedFilter[]
     */
    public function filters(): Collection
    {
        return new Collection($this->filters);
    }

    public function hasActiveFilter(): bool
    {
        return null !== Arr::first($this->filters, fn (AllowedFilter $filter) => $filter->isActive());
    }
    //endregion

    //region Sorts
    public function allowedSorts(array $sorts): static
    {
        foreach ($sorts as $sort) {
            $sort = AllowedSort::wrap($sort);

            $this->addComponent($this->sorts, $sort->name(), $sort, 'sort');
        }

        return $this;
    }

    /**
     * @return Collection|AllowedSort[]
     */
    public function sorts(): Collection
    {
        return new Collection($this->sorts);
    }
    //endregion

    //region Constraints
    public function addConstraint(Constraint|Closure $constraint): static
    {
        if ($constraint instanceof Closure) {
            $constraint = new CallbackConstraint($constraint);
        }

        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @return Collection|Constraint[]
     */
    public function constraints(): Collection
    {
        return new Collection($this->constraints);
    }

    public function where(string $field, mixed $operator, mixed $value = null): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    public function whereNot(string $field, mixed $value): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    public function whereIn(string $field, array $values = []): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    public function whereNotIn(string $field, array $values = []): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    public function whereNull(string $field): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    public function whereNotNull(string $field): static
    {
        return $this->addStandardConstraint(__FUNCTION__, func_get_args());
    }

    private function addStandardConstraint(string $method, array $params): static
    {
        return $this->addConstraint(
            fn (BoolQuery $query) => $query->{$method}(...$params)
        );
    }
    //endregion

    //region Aggregates
    public function allowedAggregates(array $aggregates): static
    {
        foreach ($aggregates as $aggregate) {
            $aggregate = AllowedAggregate::wrap($aggregate);

            $this->addComponent($this->aggregates, $aggregate->name(), $aggregate, 'aggregate');
        }

        return $this;
    }

    /**
     * @return Collection|AllowedAggregate[]
     */
    public function aggregates(): Collection
    {
        return new Collection($this->aggregates);
    }
    //endregion

    //region Facets
    public function allowedFacets(array $facets): static
    {
        foreach ($facets as $facet) {
            $facet = AllowedFacet::wrap($facet);

            $this->addComponent($this->facets, $facet->name(), $facet, 'facet');
        }

        return $this;
    }

    /**
     * @return Collection<string,AllowedFacet>
     */
    public function facets(): Collection
    {
        return new Collection($this->facets);
    }

    public function hasActiveFacet(): bool
    {
        return null !== Arr::first($this->facets, fn (AllowedFacet $facet) => $facet->isActive());
    }

    public function activeFacets(): Collection
    {
        return $this->facets()->filter(fn (AllowedFacet $facet) => $facet->isActive());
    }
    //endregion

    //region Collapse
    public function allowedCollapses(array $collapses): static
    {
        foreach ($collapses as $collapse) {
            $collapse = AllowedCollapse::wrap($collapse);

            $this->addComponent($this->collapses, $collapse->name(), $collapse, 'collapse');
        }

        return $this;
    }

    /**
     * @return Collection<int,AllowedCollapse>
     */
    public function collapses(): Collection
    {
        return new Collection($this->collapses);
    }
    //endregion

    private function addComponent(array &$target, string $name, mixed $component, string $type): void
    {
        if (array_key_exists($name, $target)) {
            throw ComponentExistsException::{$type}($name);
        }

        $target[$name] = $component;
    }
}
