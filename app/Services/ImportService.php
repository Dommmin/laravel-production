<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\ContactsImport;
use App\Repositories\ImportJobRepository;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class ImportService
{
    public function __construct(
        private readonly ImportJobRepository $importJobRepository,
    ) {}

    public function handleImport(UploadedFile $file): void
    {
        $filename = $file->getClientOriginalName();
        Excel::queueImport(new ContactsImport($filename, $this->importJobRepository), $file);
    }
}
