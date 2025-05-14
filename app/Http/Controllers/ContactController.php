<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactImportRequest;
use App\Models\Contact;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContactsImport;
use App\Exports\ContactsExport;
use App\Events\ContactImportFinished;

class ContactController extends Controller
{
    public function index()
    {
        return inertia('Contacts/Index', [
            'contacts' => Contact::latest()->simplePaginate(20),
        ]);
    }

    public function import(StoreContactImportRequest $request)
    {
        $file = $request->file('file');

        Excel::queueImport(new ContactsImport, $file)
            ->chain([
                function () {
                    event(new ContactImportFinished());
                }
            ]);

        return to_route('contacts.index')->with('success', 'Contact started importing.');
    }

    public function export()
    {
        return Excel::download(new ContactsExport, 'contacts.xlsx');
    }
}
