@extends('pdfs.layouts.pdf')

@section('title', 'Invoice (Spatie Laravel PDF)')

@section('content')
    <div class="max-w-2xl mx-auto bg-white rounded shadow p-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">INVOICE</h1>
                <p class="text-gray-500">Seller: <span class="font-semibold">{{ $user['name'] }}</span></p>
                <p class="text-gray-500">Email: {{ $user['email'] }}</p>
            </div>
            <div class="text-right">
                <p class="text-gray-500">Date: {{ now()->toDateString() }}</p>
                <p class="text-gray-500">Invoice No: INV/{{ now()->format('Y') }}/{{ now()->format('m') }}/001</p>
            </div>
        </div>
        <table class="w-full mb-8 text-sm border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-2 border">#</th>
                    <th class="p-2 border text-left">Item</th>
                    <th class="p-2 border">Quantity</th>
                    <th class="p-2 border">Unit Price</th>
                    <th class="p-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                    <tr class="border-b">
                        <td class="p-2 border text-center">{{ $i+1 }}</td>
                        <td class="p-2 border">{{ $item['name'] }}</td>
                        <td class="p-2 border text-center">{{ $item['qty'] }}</td>
                        <td class="p-2 border text-right">${{ number_format($item['price'], 2) }}</td>
                        <td class="p-2 border text-right">${{ number_format($item['qty'] * $item['price'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="p-2 border text-right font-bold">Total</td>
                    <td class="p-2 border text-right font-bold">${{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <div class="text-xs text-gray-400 mt-8">Generated automatically by Spatie Laravel PDF + TailwindCSS</div>
    </div>
@endsection 