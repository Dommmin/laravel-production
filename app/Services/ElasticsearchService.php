<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;

class ElasticsearchService
{
    public function client()
    {
        return ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->build();
    }

    public function createArticlesIndex()
    {
        $params = [
            'index' => 'articles',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'autocomplete' => [
                                'tokenizer' => 'autocomplete',
                                'filter' => ['lowercase']
                            ]
                        ],
                        'tokenizer' => [
                            'autocomplete' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 1,
                                'max_gram' => 20,
                                'token_chars' => ['letter', 'digit']
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                            'search_analyzer' => 'standard'
                        ],
                        'tags' => [
                            'type' => 'keyword'
                        ],
                        'user' => [
                            'type' => 'keyword'
                        ],
                        'location' => [
                            'type' => 'geo_point'
                        ],
                        'city_name' => [
                            'type' => 'keyword'
                        ]
                    ]
                ]
            ]
        ];
        // Usuń indeks jeśli istnieje
        if ($this->client()->indices()->exists(['index' => 'articles'])->asBool()) {
            $this->client()->indices()->delete(['index' => 'articles']);
        }
        $this->client()->indices()->create($params);
    }
}
