<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Specification\Stubs;

use Ensi\LaravelElasticQuerySpecification\Specification\CompositeSpecification;
use Ensi\LaravelElasticQuerySpecification\Specification\Specification;
use Ensi\LaravelElasticQuerySpecification\Specification\Visitor;

class VisitorStub implements Visitor
{
    public ?Specification $rootSpecification = null;
    public array $nestedSpecifications = [];
    public array $nestedFields = [];
    public bool $done = false;

    public function visitRoot(Specification $specification): void
    {
        expect($this->rootSpecification)->toBeNull();

        $this->rootSpecification = $specification;
    }

    public function visitNested(string $field, Specification $specification): void
    {
        $this->nestedFields[] = $field;
        $this->nestedSpecifications[] = $specification;
    }

    public function done(): void
    {
        $this->done = true;
    }

    public static function inspect(CompositeSpecification $spec): self
    {
        $visitor = new self();
        $spec->accept($visitor);

        return $visitor;
    }
}
