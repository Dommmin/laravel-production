<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = Cart::with('product')->get();

        $paymentMethods = [
            'Przelewy24',
            'PayPal',
            'Blik',
            'Płatność online',
            'Płatność kartą',
        ];

        $deliveryMethods = [
            'Inpost',
            'DHL',
            'DPD',
            'Orlen Paczka',
        ];

        return Inertia::render('Checkout/Index', [
            'cartItems' => $cartItems,
            'paymentMethods' => $paymentMethods,
            'deliveryMethods' => $deliveryMethods
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'courier' => 'required|string',
            'paymentMethod' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            $cartItems = Cart::with('product')->where('user_id', auth()->id())->get();

            $totalPrice = $cartItems->sum(function ($cartItem) {
                return $cartItem->quantity * $cartItem->product->price;
            });

            $order = Order::create([
                'user_id' => auth()->id(),
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            Cart::where('user_id', auth()->id())->delete();
        });

        return redirect()->route('home')->with('success', 'Order placed successfully!');
    }
}
