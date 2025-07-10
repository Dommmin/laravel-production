<?php

namespace App\Http\Controllers;

use App\Events\ClickEvent;
use App\Models\Click;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClickController extends Controller
{
    public function index(Request $request)
    {
        $click = Click::first();

        return Inertia::render('Click', [
            'click' => $click
        ]);
    }

    public function click(Request $request)
    {
        Click::firstOrCreate()->increment('times');

        broadcast(new ClickEvent());

        return redirect()->route('clicks.index');
    }

    public function reset(Request $request)
    {
        Click::firstOrCreate()->update(['times' => 0]);

        return redirect()->route('clicks.index');
    }
}
