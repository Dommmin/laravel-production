<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileController;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfDemoController;
use App\Http\Controllers\ContactController;

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
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chat}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chat}', [ChatController::class, 'store'])->name('chat.send');
});

Route::get('/pdf/spatie', [PdfDemoController::class, 'spatie'])->name('pdf.spatie');
Route::get('/pdf/dompdf', [PdfDemoController::class, 'dompdf'])->name('pdf.dompdf');

Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
Route::post('/contacts/import', [ContactController::class, 'import'])->name('contacts.import');
Route::get('/contacts/export', [ContactController::class, 'export'])->name('contacts.export');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
