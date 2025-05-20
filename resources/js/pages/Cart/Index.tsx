import React from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

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
        <h1 className="text-2xl font-bold mb-4">Cart</h1>
        <ul className="divide-y divide-gray-200">
          {cartItems.map((item) => (
            <li key={item.id} className="py-4 flex justify-between items-center">
              <div>
                <div className="font-semibold">{item.product.name}</div>
                <div className="text-gray-600">${item.product.price} x {item.quantity}</div>
              </div>
              <div className="flex items-center">
                <button
                  className="bg-gray-200 text-gray-700 py-1 px-3 rounded hover:bg-gray-300"
                  onClick={() => updateQuantity(item.id, item.quantity + 1)}
                >
                  +
                </button>
                <button
                  className="bg-gray-200 text-gray-700 py-1 px-3 rounded hover:bg-gray-300 mx-2"
                  onClick={() => updateQuantity(item.id, item.quantity - 1)}
                >
                  -
                </button>
                <button
                  className="bg-red-500 text-white py-1 px-4 rounded hover:bg-red-600"
                  onClick={() => removeFromCart(item.id)}
                >
                  Remove
                </button>
              </div>
            </li>
          ))}
        </ul>
        <button
          className="mt-4 bg-green-500 text-white py-2 px-6 rounded hover:bg-green-600"
          onClick={() => router.get('/checkout')}
        >
          Proceed to Checkout
        </button>
      </div>
    </AppLayout>
  );
};

export default Cart;