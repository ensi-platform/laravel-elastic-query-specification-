<?php

namespace Ensi\LaravelElasticQuerySpecification\Tests\Data\Seeds;

class ProductIndexSeeder extends IndexSeeder
{
    protected string $indexName = 'test_spec_products';

    protected array $fixtures = ['products.json'];

    protected array $mappings = [
        'properties' => [
            'product_id' => ['type' => 'keyword'],
            'active' => ['type' => 'boolean'],

            'name' => ['type' => 'keyword', 'copy_to' => 'search_name'],
            'search_name' => ['type' => 'text', 'analyzer' => 'default'],
            'description' => ['type' => 'text'],

            'code' => ['type' => 'keyword'],
            'tags' => ['type' => 'keyword'],
            'rating' => ['type' => 'integer'],
            'package' => ['type' => 'keyword'],
            'vat' => ['type' => 'integer'],

            'cashback' => [
                'type' => 'object',
                'properties' => [
                    'active' => ['type' => 'boolean'],
                    'value' => ['type' => 'integer'],
                ],
            ],

            'offers' => [
                'type' => 'nested',
                'properties' => [
                    'seller_id' => ['type' => 'keyword'],
                    'active' => ['type' => 'boolean'],
                    'price' => ['type' => 'double'],
                ],
            ],
        ],
    ];

    protected array $settings = [
        'analysis' => [
            'filter' => [
                'english_stop' => [
                    'type' => 'stop',
                    'stopwords' => '_english_',
                ],
                'english_stemmer' => [
                    'type' => 'stemmer',
                    'language' => 'english',
                ],
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => ['html_strip'],
                    'tokenizer' => 'standard',
                    'filter' => [
                        'lowercase',
                        'english_stop',
                        'english_stemmer',
                    ],
                ],
            ],
        ],
    ];
}
