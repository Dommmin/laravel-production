import AppLayout from '@/layouts/app-layout';
import { router } from '@inertiajs/react';
import React from 'react';

interface Product {
    id: number;
    name: string;
    price: number;
}

interface ProductsProps {
    products: Product[];
}

const Products: React.FC<ProductsProps> = ({ products }) => {
    const addToCart = (productId: number) => {
        router.post(
            '/cart',
            { product_id: productId, quantity: 1 },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Optionally, you can show a notification or update the cart count here
                },
            },
        );
    };

    return (
        <AppLayout>
            <div className="container mx-auto p-4">
                <h1 className="mb-4 text-2xl font-bold">Products</h1>
                <ul className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {products.map((product) => (
                        <li key={product.id} className="rounded border p-4 shadow">
                            <div className="font-semibold">{product.name}</div>
                            <div className="text-gray-600">${product.price}</div>
                            <button className="mt-2 rounded bg-blue-500 px-4 py-1 text-white hover:bg-blue-600" onClick={() => addToCart(product.id)}>
                                Add to Cart
                            </button>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    );
};

export default Products;
