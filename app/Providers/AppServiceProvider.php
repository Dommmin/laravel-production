<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Article;
use App\Observers\ArticleObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch();

        if (App::environment('local')) {
            Model::shouldBeStrict();
        }

        if (App::environment('production', 'staging')) {
            URL::forceScheme('https');
        }

        if (! app()->runningUnitTests()) {
            Article::observe(ArticleObserver::class);
        }
    }
}
