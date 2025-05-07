<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ArticleSearchService;
use Inertia\Inertia;

class ArticleSearchController extends Controller
{
    public function search(Request $request, ArticleSearchService $searchService)
    {
        $result = $searchService->search($request->all());
        return Inertia::render('dashboard', $result);
    }
}
