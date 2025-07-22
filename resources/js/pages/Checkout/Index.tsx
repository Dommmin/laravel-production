import AppLayout from '@/layouts/app-layout';
import { useForm } from '@inertiajs/react';
import React, { useState } from 'react';

interface FormData {
    [key: string]: string;
    name: string;
    address: string;
    courier: string;
    paymentMethod: string;
}

interface CheckoutProps {
    paymentMethods: string[];
    deliveryMethods: string[];
}

const Checkout = ({ paymentMethods, deliveryMethods }: CheckoutProps) => {
    const { post, data, setData } = useForm<Record<string, string>>({
        name: '',
        address: '',
        courier: 'Inpost',
        paymentMethod: '',
    });

    const [step, setStep] = useState(1);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setData(name, value);
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/checkout');
    };

    const nextStep = () => setStep((prev) => prev + 1);
    const prevStep = () => setStep((prev) => prev - 1);

    return (
        <AppLayout>
            <div className="container mx-auto p-4">
                <h1 className="mb-4 text-2xl font-bold">Checkout</h1>
                {step === 1 && (
                    <div className="space-y-4">
                        <input
                            type="text"
                            name="name"
                            placeholder="Name"
                            onChange={handleChange}
                            required
                            className="w-full rounded border border-gray-300 p-2"
                        />
                        <input
                            type="text"
                            name="address"
                            placeholder="Address"
                            onChange={handleChange}
                            required
                            className="w-full rounded border border-gray-300 p-2"
                        />
                        <button className="w-full rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600" onClick={nextStep}>
                            Next
                        </button>
                    </div>
                )}
                {step === 2 && (
                    <div className="space-y-4">
                        <select name="deliveryMethod" onChange={handleChange} className="w-full rounded border border-gray-300 p-2">
                            {deliveryMethods.map((method, index) => (
                                <option key={index} value={method}>
                                    {method}
                                </option>
                            ))}
                        </select>
                        <select name="paymentMethod" onChange={handleChange} className="w-full rounded border border-gray-300 p-2">
                            {paymentMethods.map((method, index) => (
                                <option key={index} value={method}>
                                    {method}
                                </option>
                            ))}
                        </select>
                        <div className="flex justify-between">
                            <button className="rounded bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300" onClick={prevStep}>
                                Back
                            </button>
                            <button className="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600" onClick={handleSubmit}>
                                Place Order
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default Checkout;
