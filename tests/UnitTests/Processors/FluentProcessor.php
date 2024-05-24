<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Processors;

use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Specification\Visitor;
use Illuminate\Support\Collection;

class FluentProcessor
{
    public Visitor $processor;

    public function __construct(string $processorClass, mixed ...$parameters)
    {
        $parameters = array_map(
            fn ($param) => is_array($param) ? new Collection($param) : $param,
            $parameters
        );

        $this->processor = new $processorClass(...$parameters);
    }

    public static function new(string $processorClass, mixed ...$parameters): self
    {
        return new self($processorClass, ...$parameters);
    }

    public function visitRoot(Specification $specification): self
    {
        $this->processor->visitRoot($specification);

        return $this;
    }

    public function visitNested(string $field, Specification $specification): self
    {
        $this->processor->visitNested($field, $specification);

        return $this;
    }

    public function done(): self
    {
        $this->processor->done();

        return $this;
    }
}
