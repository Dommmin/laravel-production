<?php

namespace App\Imports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;

class ContactsImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    public function model(array $row)
    {
        return new Contact([
            'name'    => $row['name'] ?? null,
            'email'   => $row['email'] ?? null,
            'phone'   => $row['phone'] ?? null,
            'company' => $row['company'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
