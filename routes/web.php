<?php

use App\Http\Controllers\FileController;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ArticleSearchController;
use App\Http\Controllers\ArticleController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('/dashboard', [ArticleSearchController::class, 'search'])->name('dashboard')->middleware('auth');

//Route::middleware(['auth', 'verified'])->group(function () {
//    Route::get('dashboard', function () {
//        return Inertia::render('dashboard');
//    })->name('dashboard');
//});

// Health check endpoint
Route::get('/up', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/test-mail', function () {
    try {
        Mail::to('text@example.com')->queue(new TestMail());
    } catch (Exception $e) {
        return $e->getMessage();
    }

    return 'Mail sent';
});

Route::resource('/files', FileController::class)
    ->only(['index', 'store', 'destroy'])
    ->middleware('auth');

Route::get('/search', [ArticleSearchController::class, 'search']);

Route::get('/articles/create', [ArticleController::class, 'create'])->name('articles.create');
Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');

Route::get('/articles/{article}/similar', [ArticleController::class, 'similar']);

Route::get('/articles/aggregation/cities', [ArticleController::class, 'cityAggregation']);

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
