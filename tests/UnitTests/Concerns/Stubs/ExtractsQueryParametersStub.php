<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\UnitTests\Concerns\Stubs;

use Ensi\LaravelElasticQuerySpecification\Concerns\ExtractsQueryParameters;

class ExtractsQueryParametersStub
{
    use ExtractsQueryParameters;

    public function __construct(public array $source)
    {
    }

    protected function extract(string $key): mixed
    {
        return $this->source[$key] ?? null;
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return $default ?? $key;
    }
}
