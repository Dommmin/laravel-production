<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactImportRequest;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContactsImport;
use App\Exports\ContactsExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Contacts/Index', [
            'contacts' => Contact::paginate(10),
        ]);
    }

    public function import(StoreContactImportRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        Excel::queueImport(new ContactsImport($filename), $file);

        return back();
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ContactsExport, 'contacts.xlsx');
    }
}
