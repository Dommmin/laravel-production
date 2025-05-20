import React, { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

interface FormData {
  [key: string]: string;
  name: string;
  address: string;
  courier: string;
  paymentMethod: string;
}

const Checkout: React.FC = () => {
  const { post, data, setData } = useForm<Record<string, string>>({
    name: '',
    address: '',
    courier: 'Inpost',
    paymentMethod: 'Przelewy24',
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
        <h1 className="text-2xl font-bold mb-4">Checkout</h1>
        {step === 1 && (
          <div className="space-y-4">
            <input
              type="text"
              name="name"
              placeholder="Name"
              onChange={handleChange}
              required
              className="w-full p-2 border border-gray-300 rounded"
            />
            <input
              type="text"
              name="address"
              placeholder="Address"
              onChange={handleChange}
              required
              className="w-full p-2 border border-gray-300 rounded"
            />
            <button
              className="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600"
              onClick={nextStep}
            >
              Next
            </button>
          </div>
        )}
        {step === 2 && (
          <div className="space-y-4">
            <select
              name="courier"
              onChange={handleChange}
              className="w-full p-2 border border-gray-300 rounded"
            >
              <option value="Inpost">Inpost</option>
              <option value="DHL">DHL</option>
              <option value="DPD">DPD</option>
              <option value="Orlen Paczka">Orlen Paczka</option>
            </select>
            <select
              name="paymentMethod"
              onChange={handleChange}
              className="w-full p-2 border border-gray-300 rounded"
            >
              <option value="Przelewy24">Przelewy24</option>
              <option value="Apple Pay">Apple Pay</option>
              <option value="Google Pay">Google Pay</option>
              <option value="Blik">Blik</option>
              <option value="Deferred Payment">Deferred Payment</option>
            </select>
            <div className="flex justify-between">
              <button
                className="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300"
                onClick={prevStep}
              >
                Back
              </button>
              <button
                className="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600"
                onClick={handleSubmit}
              >
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