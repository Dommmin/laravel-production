<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use Illuminate\Http\Request;
use App\Models\Cart;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->get();
        return Inertia::render('Cart/Index', ['cartItems' => $cartItems]);
    }

    public function store(StoreCartRequest $request)
    {
        $validated = $request->validated();

        $cartItem = Cart::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ],
        );

        return redirect()->route('cart.index');
    }

    public function update(UpdateCartRequest $request, Cart $cart)
    {
        $cart->update($request->validated());

        return redirect()->route('cart.index');
    }

    public function destroy(Cart $cart)
    {
        $cart->delete();

        return redirect()->route('cart.index');
    }
}
