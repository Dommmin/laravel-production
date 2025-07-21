import AppLayout from '@/layouts/app-layout';
import { router } from '@inertiajs/react';
import React from 'react';

interface CartItem {
    id: number;
    product: {
        name: string;
        price: number;
    };
    quantity: number;
}

interface CartProps {
    cartItems: CartItem[];
}

const Cart: React.FC<CartProps> = ({ cartItems }) => {
    const updateQuantity = (itemId: number, quantity: number) => {
        router.put(`/cart/${itemId}`, { quantity });
    };

    const removeFromCart = (itemId: number) => {
        router.delete(`/cart/${itemId}`);
    };

    return (
        <AppLayout>
            <div className="container mx-auto p-4">
                <h1 className="mb-4 text-2xl font-bold">Cart</h1>
                <ul className="divide-y divide-gray-200">
                    {cartItems.map((item) => (
                        <li key={item.id} className="flex items-center justify-between py-4">
                            <div>
                                <div className="font-semibold">{item.product.name}</div>
                                <div className="text-gray-600">
                                    ${item.product.price} x {item.quantity}
                                </div>
                            </div>
                            <div className="flex items-center">
                                <button
                                    className="rounded bg-gray-200 px-3 py-1 text-gray-700 hover:bg-gray-300"
                                    onClick={() => updateQuantity(item.id, item.quantity + 1)}
                                >
                                    +
                                </button>
                                <button
                                    className="mx-2 rounded bg-gray-200 px-3 py-1 text-gray-700 hover:bg-gray-300"
                                    onClick={() => updateQuantity(item.id, item.quantity - 1)}
                                >
                                    -
                                </button>
                                <button className="rounded bg-red-500 px-4 py-1 text-white hover:bg-red-600" onClick={() => removeFromCart(item.id)}>
                                    Remove
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
                <button className="mt-4 rounded bg-green-500 px-6 py-2 text-white hover:bg-green-600" onClick={() => router.get('/checkout')}>
                    Proceed to Checkout
                </button>
            </div>
        </AppLayout>
    );
};

export default Cart;
