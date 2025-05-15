<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ContactsExport;
use App\Http\Requests\StoreContactImportRequest;
use App\Models\Contact;
use App\Services\ImportService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContactController extends Controller
{
    public function __construct(
        private readonly ImportService $importService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Contacts/Index', [
            'contacts' => Contact::paginate(10),
        ]);
    }

    public function import(StoreContactImportRequest $request): RedirectResponse
    {
        $this->importService->handleImport($request->file('file'));

        return back();
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(new ContactsExport, 'contacts.xlsx');
    }
}
