<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\Data\Seeds;

use Ensi\LaravelElasticQuery\ElasticClient;
use Illuminate\Support\Facades\ParallelTesting;

abstract class IndexSeeder
{
    protected string $indexName = '';
    protected array $mappings = [];
    protected array $settings = [];
    protected array $fixtures = [];

    protected bool $recreate;

    protected ?ElasticClient $client;

    public function __construct()
    {
        $this->recreate = config('tests.recreate_index', true);
    }

    public function setClient(ElasticClient $client): void
    {
        $this->client = $client;
    }

    public static function run(): void
    {
        SeedRunner::getInstance()->run(static::class);
    }

    public function call(): void
    {
        $exists = $this->isIndexExists();

        if ($exists && $this->recreate) {
            $this->dropIndex();
            $exists = false;
        }

        if ($exists) {
            return;
        }

        $this->createIndex();
        $this->loadFixtures();
    }

    protected function isIndexExists(): bool
    {
        return $this->client->indicesExists($this->getIndexName());
    }

    protected function dropIndex(): void
    {
        $this->client->indicesDelete($this->getIndexName());
    }

    protected function createIndex(): void
    {
        $settings = [];

        if (!empty($this->mappings)) {
            $settings['mappings'] = $this->mappings;
        }

        if (!empty($this->settings)) {
            $settings['settings'] = $this->settings;
        }

        $this->client->indicesCreate($this->getIndexName(), $settings);
    }

    protected function loadFixtures(): void
    {
        $baseDir = __DIR__ . '/fixtures/';

        $hasChanges = collect($this->fixtures)
            ->reduce(
                fn (bool $carry, string $fixture) => $this->loadFixture($baseDir . $fixture) || $carry,
                false
            );

        if ($hasChanges) {
            $this->client->indicesRefresh($this->getIndexName());
        }
    }

    protected function loadFixture(string $path): bool
    {
        $documents = json_decode(file_get_contents($path), true);

        if (empty($documents)) {
            return false;
        }

        $body = collect($documents)
            ->flatMap(fn (array $document, int $index) => $this->documentToCommand($document, $index))
            ->toArray();

        $this->client->bulk($this->getIndexName(), $body);

        return true;
    }

    protected function documentToCommand(array $document, int $id): array
    {
        return [
            ['index' => ['_index' => $this->getIndexName(), '_id' => $id]],
            $document,
        ];
    }

    protected function getIndexName(): string
    {
        return $this->indexName . (ParallelTesting::token() ?: 0);
    }
}