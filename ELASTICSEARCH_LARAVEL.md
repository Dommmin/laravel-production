# How to Integrate Elasticsearch with Laravel: A Step-by-Step Guide

Elasticsearch is a powerful search engine that can supercharge your Laravel application's search capabilities. In this article, I'll walk you through a real-world integration of Elasticsearch in a Laravel project, including service implementation, DTOs, and robust unit testing. All code examples are based on a production-ready setup, and you’ll find a link to the full source code at the end.

---

## 1. Why Elasticsearch?

Laravel's Eloquent is great for most queries, but when you need full-text search, aggregations, or geo-search, Elasticsearch is the go-to solution. Integrating it cleanly with Laravel ensures your codebase remains maintainable and testable.

---

## 2. Installation and Configuration

First, require the official Elasticsearch PHP client:

```bash
composer require elasticsearch/elasticsearch
```

Then, add Elasticsearch to your `docker-compose.yml` file:

```yaml
services:
    ...
    elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:9.0.1
    container_name: laravel_elasticsearch
    environment:
        - discovery.type=single-node
        - ES_JAVA_OPTS=-Xms1g -Xmx1g
        - xpack.security.enabled=false
        - xpack.monitoring.collection.enabled=true
    ports:
        - "9200:9200"
    volumes:
        - esdata:/usr/share/elasticsearch/data
    networks:
        - laravel-network
        
voluems:
    ...
    esdata:
      driver: local
```
---

Next, create a configuration file for Elasticsearch:

```bash
touch config/elasticsearch.php
```

```php
<?php

return [
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
];
```

## 3. Service Layer: Wrapping the Elasticsearch Client

To keep your code clean and testable, encapsulate all Elasticsearch logic in a dedicated service.

**app/Services/ElasticsearchService.php**
```php
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

    public function index(array $params)
    {
        return $this->client()->index($params);
    }
}
```

---

## 4. Data Transfer Object (DTO) for Search Filters

Using a DTO (with [spatie/laravel-data](https://github.com/spatie/laravel-data)) keeps your search logic clean and type-safe.

**app/DTO/ArticleFilterData.php**
```php
<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class ArticleFilterData extends Data
{
    public function __construct(
        public ?string $q = '',
        public ?string $tag = null,
        public ?string $city = null,
        public ?int $radius = null,
        public ?float $lat = null,
        public ?float $lon = null,
        public ?int $page = 1,
        public ?int $size = 20,
    ) {}
}
```

---

## 5. Search Service: Business Logic for Article Search

This service builds the Elasticsearch query based on the DTO and returns structured results.

**app/Services/ArticleSearchService.php**
```php
<?php

namespace App\Services;

use App\DTO\ArticleFilterData;
use Illuminate\Support\Arr;

readonly class ArticleSearchService
{
    public function __construct(private ElasticsearchService $es) {}

    public function search(ArticleFilterData $filters): array
    {
        $queryBody = [
            'index' => 'articles',
            'from' => ($filters->page - 1) * $filters->size,
            'size' => $filters->size,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $this->buildMustQueries($filters),
                        'filter' => $this->buildFilterQueries($filters),
                    ],
                ],
            ],
        ];

        $results = $this->es->client()->search($queryBody);

        return [
            'articles' => collect($results['hits']['hits'])->pluck('_source')->all(),
            'total' => $results['hits']['total']['value'] ?? 0,
            'filters' => $filters->toArray(),
        ];
    }

    private function buildMustQueries(ArticleFilterData $filters): array
    {
        $must = [];

        if ($filters->q) {
            $must[] = mb_strlen($filters->q) <= 2
                ? ['wildcard' => ['title' => "*{$filters->q}*"]]
                : [
                    'multi_match' => [
                        'query' => $filters->q,
                        'fields' => ['title^2', 'tags'],
                        'fuzziness' => 'auto',
                        'operator' => 'and',
                        'minimum_should_match' => '100%',
                    ],
                ];
        }

        if ($filters->tag) {
            $must[] = ['term' => ['tags' => $filters->tag]];
        }

        return $must;
    }

    private function buildFilterQueries(ArticleFilterData $filters): array
    {
        $filter = [];

        if ($filters->city) {
            $filter[] = ['term' => ['city_name' => $filters->city]];
        }

        if ($filters->lat && $filters->lon && $filters->radius > 0) {
            $filter = array_filter($filter, fn($f) => !Arr::has($f, 'term.city_name'));
            $filter[] = [
                'geo_distance' => [
                    'distance' => "{$filters->radius}km",
                    'location' => [
                        'lat' => $filters->lat,
                        'lon' => $filters->lon,
                    ],
                ],
            ];
        }

        return $filter;
    }
}
```

---
## 6. Command to Create the Index
**app/Console/Commands/ReindexArticlesElasticsearch.php**

```php
<?php

namespace App\Console\Commands;

use App\Jobs\ReindexArticles;
use App\Services\ElasticsearchService;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Console\Command;

class ReindexArticlesElasticsearch extends Command
{
    protected $signature = 'es:reindex-articles';

    protected $description = 'Creates the articles index with mapping and reindexes articles to Elasticsearch';

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function handle(ElasticsearchService $es): void
    {
        $this->info('Creating index articles with mapping...');
        $es->createArticlesIndex();
        $this->info('Reindexing articles...');
        ReindexArticles::dispatchSync();
        $this->info('Done!');
    }
}
```
---
## 7. Job for Reindexing Articles
**app/Jobs/ReindexArticles.php**
```php
<?php

namespace App\Jobs;

use App\Models\Article;
use App\Models\User;
use App\Services\ElasticsearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReindexArticles implements ShouldQueue
{
    use Queueable;

    public function handle(ElasticsearchService $es): void
    {
        Article::with(['user', 'tags'])->chunk(100, function ($articles) use ($es) {
            foreach ($articles as $article) {

                /** @var User $user */
                $user = $article->user;

                $es->index([
                    'index' => 'articles',
                    'id' => $article->id,
                    'body' => [
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'content' => $article->content,
                        'user' => $user->name,
                        'tags' => $article->tags->pluck('name')->toArray(),
                        'location' => $article->location ?? null,
                        'city_name' => $article->city_name ?? null,
                    ],
                ]);
            }
        });
    }
}
```
---

## 8. Observer for Article Model
**app/Observers/ArticleObserver.php**
```php
<?php

namespace App\Observers;

use App\Jobs\ReindexArticles;
use App\Models\Article;
use App\Services\ElasticsearchService;

class ArticleObserver
{
    public function deleted(Article $article): void
    {
        $es = app(ElasticsearchService::class)->client();

        try {
            $es->delete([
                'index' => 'articles',
                'id' => $article->id,
            ]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
        }
    }

    public function saved(Article $article)
    {
        ReindexArticles::dispatch();
    }
}
```
    
---
## 9. Summary

- **Service Layer**: Encapsulate Elasticsearch logic for maintainability.
- **DTOs**: Use data objects for clean, type-safe parameter passing.

This approach keeps your Laravel codebase clean, testable, and ready for production-scale search features.

---

## Source Code

You can find the full implementation and more examples in the [GitHub repository](https://github.com/Dommmin/laravel-production).

---

*Happy coding! If you have questions, feel free to contact me.*
