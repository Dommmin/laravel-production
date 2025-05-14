<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ContactsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Contact::select('name', 'email', 'phone', 'company')->get();
    }

    public function headings(): array
    {
        return [
            'name',
            'email',
            'phone',
            'company',
        ];
    }
} 