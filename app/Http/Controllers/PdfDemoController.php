<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;

class PdfDemoController extends Controller
{
    public function spatie(Request $request)
    {
        $data = [
            'user' => [
                'name' => 'Dominik Nowak',
                'email' => 'dominik@example.com',
            ],
            'items' => [
                ['name' => 'Super Product', 'qty' => 2, 'price' => 199.99],
                ['name' => 'Mega Service', 'qty' => 1, 'price' => 499.00],
            ],
            'total' => 199.99 * 2 + 499.00,
        ];

        return Pdf::view('pdfs.spatie-invoice', $data)
            ->withBrowsershot(function (Browsershot $browsershot) {
                $browsershot->noSandbox()->format('A4');
            })
            ->download('spatie-invoice.pdf');
    }

    public function dompdf(Request $request)
    {
        $data = [
            'invoice_number' => 'FV/2024/05/001',
            'date' => now()->toDateString(),
            'client' => 'Jan Kowalski',
            'items' => [
                ['name' => 'Produkt A', 'qty' => 3, 'price' => 100],
                ['name' => 'Produkt B', 'qty' => 1, 'price' => 250],
            ],
            'total' => 3 * 100 + 250,
        ];

        return DomPdf::loadView('pdfs.dompdf-invoice', $data)->download('dompdf-invoice.pdf');
    }
}
