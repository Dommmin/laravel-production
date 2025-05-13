<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice (DomPDF)</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 10px; }
        th { background: #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h2>INVOICE (DomPDF)</h2>
    <p>Invoice No: <strong>{{ $invoice_number }}</strong></p>
    <p>Date: <strong>{{ $date }}</strong></p>
    <p>Client: <strong>{{ $client }}</strong></p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">${{ number_format($item['price'], 2) }}</td>
                    <td class="text-right">${{ number_format($item['qty'] * $item['price'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>${{ number_format($total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    <p style="font-size: 10px; color: #888;">Generated automatically by barryvdh/laravel-dompdf</p>
</body>
</html> 