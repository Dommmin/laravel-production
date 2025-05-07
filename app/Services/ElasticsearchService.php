<?php

namespace App\Services;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

class ElasticsearchService
{
    /**
     * @throws AuthenticationException
     */
    public function client()
    {
        return ClientBuilder::create()
            ->setHosts([config('elasticsearch.host')])
            ->build();
    }

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function createArticlesIndex(): void
    {
        $params = [
            'index' => 'articles',
            'body' => [
                'settings' => [
                    'analysis' => [
                        'analyzer' => [
                            'autocomplete' => [
                                'tokenizer' => 'autocomplete',
                                'filter' => ['lowercase'],
                            ],
                        ],
                        'tokenizer' => [
                            'autocomplete' => [
                                'type' => 'edge_ngram',
                                'min_gram' => 1,
                                'max_gram' => 20,
                                'token_chars' => ['letter', 'digit'],
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'title' => [
                            'type' => 'text',
                            'analyzer' => 'autocomplete',
                            'search_analyzer' => 'standard',
                        ],
                        'tags' => [
                            'type' => 'keyword',
                        ],
                        'user' => [
                            'type' => 'keyword',
                        ],
                        'location' => [
                            'type' => 'geo_point',
                        ],
                        'city_name' => [
                            'type' => 'keyword',
                        ],
                    ],
                ],
            ],
        ];

        if ($this->client()->indices()->exists(['index' => 'articles'])->asBool()) {
            $this->client()->indices()->delete(['index' => 'articles']);
        }

        $this->client()->indices()->create($params);
    }

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function index(array $params)
    {
        return $this->client()->index($params);
    }
}
