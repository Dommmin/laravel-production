<?php

namespace App\Models;

use App\DTO\ArticleFilterData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Article extends Model
{
    use HasFactory, HasSlug;

    protected $guarded = ['id'];

    protected $casts = [
        'location' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public static function getArticlesForIndex(ArticleFilterData $filters)
    {
        return self::query()
            ->with(['tags', 'user'])
            ->when($filters->city, fn ($query, $city) => $query->where('city_name', $city))
            ->when($filters->tag, fn ($query, $tag) => $query->whereHas('tags', fn ($query) => $query->where('name', $tag)))
            ->when($filters->q, fn ($query, $search) => $query->where(function ($query) use ($search) {
                return $query->where('title', 'like', "%{$search}%");
            }))
            ->paginate(20)
            ->withQueryString()
            ->through(function (Article $article) {
                $tags = $article->tags;
                $article->unsetRelation('tags');
                $article->setAttribute('tags', $tags->pluck('name')->toArray());

                return $article;
            });
    }
}
