<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileController;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/up', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('/test-mail', function () {
    try {
        Mail::to('text@example.com')->queue(new TestMail);
    } catch (Exception $e) {
        return $e->getMessage();
    }

    return 'Mail sent';
});

Route::resource('/files', FileController::class)
    ->only(['index', 'store', 'destroy'])
    ->middleware('auth');

Route::get('/', [ArticleController::class, 'index'])->name('home');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/articles/{article}/similar', [ArticleController::class, 'similar'])->name('articles.similar');
Route::get('/articles/aggregation/cities', [ArticleController::class, 'cityAggregation'])->name('articles.aggregation.cities');

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [\App\Http\Controllers\ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chat}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chat}', [\App\Http\Controllers\ChatController::class, 'store'])->name('chat.send');
    Route::post('/chat/find-or-create/{user}', [\App\Http\Controllers\ChatController::class, 'findOrCreate'])->name('chat.findOrCreate');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
