<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->get();
        return Inertia::render('Checkout/Index', ['cartItems' => $cartItems]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'courier' => 'required|string',
            'paymentMethod' => 'required|string',
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_price' => Cart::sum('quantity * product.price'),
            'status' => 'pending',
        ]);

        Cart::truncate();

        return redirect()->route('home')->with('success', 'Order placed successfully!');
    }
}
