<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->get();
        return Inertia::render('Cart/Index', ['cartItems' => $cartItems]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::updateOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return redirect()->route('cart.index');
    }

    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index');
    }

    public function destroy(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('cart.index');
    }
}
