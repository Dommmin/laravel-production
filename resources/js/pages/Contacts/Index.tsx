import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { type Contact, type ImportError } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Loader2 } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { toast } from 'sonner';

interface ContactData {
    data: Contact[];
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    last_page: number;
}

type FormData = {
    file: File | null;
};

export default function ContactsIndex({ contacts }: { contacts: ContactData }) {
    const { setData, post, processing, errors } = useForm<FormData>({
        file: null,
    });
    const [importing, setImporting] = useState(false);
    const [importErrors, setImportErrors] = useState<string[]>([]);

    const formatImportError = (error: ImportError): string => {
        const parts: string[] = [];
        if (error.row) parts.push(`Row ${error.row}`);
        if (error.errors && error.errors.length > 0) {
            parts.push(error.errors.join(', '));
        }
        return parts.length > 0 ? parts.join(' - ') : 'Invalid data format';
    };

    useEffect(() => {
        window.Echo.channel('imports')
            .listen('.ContactImportFinished', () => {
                setImporting(false);
                setImportErrors([]);
                toast.success('Import finished successfully');
                router.reload({ only: ['contacts'] });
            })
            .listen('.ContactImportFailed', (event) => {
                setImporting(false);
                if (event.failures && Array.isArray(event.failures)) {
                    const formattedErrors = event.failures.map(formatImportError);
                    setImportErrors(formattedErrors);
                    toast.error('Import failed with validation errors');
                } else {
                    toast.error('Something went wrong');
                }
                router.reload({ only: ['contacts'] });
            })
            .listen('.ContactImportError', () => {
                setImporting(false);
                setImportErrors([]);
                toast.error('Something went wrong');
                router.reload({ only: ['contacts'] });
            });
        return () => {
            window.Echo.leave('contacts-import');
        };
    }, []);

    const handleImport = (event: React.FormEvent) => {
        event.preventDefault();
        setImportErrors([]);

        post(route('contacts.import'), {
            onSuccess: () => {
                setImporting(true);
            },
            onError: () => {
                toast.error('Something went wrong');
            },
        });
    };

    const handleExport = () => {
        window.location.href = '/contacts/export';
    };

    return (
        <AppLayout>
            <Head title="Contacts" />
            <h1 className="mb-4 text-2xl font-bold">Contacts</h1>
            <form onSubmit={handleImport} className="mb-6 flex items-center gap-2">
                <Input type="file" accept=".csv,.xlsx" onChange={(e) => setData('file', e.target.files?.[0] || null)} className="max-w-xs" />
                <Button type="submit" disabled={processing || importing} variant="default">
                    {processing || importing ? 'Importing...' : 'Import'}
                </Button>
                <Button type="button" onClick={handleExport} variant="secondary">
                    Export
                </Button>
                <a href="./SampleImport.csv" download className="ml-2 text-sm underline">
                    Download sample CSV
                </a>
            </form>
            {importing && (
                <div className="mb-4 flex items-center gap-2">
                    <Loader2 className="animate-spin" />
                    <span>Import in progress... Please wait.</span>
                </div>
            )}
            {errors.file && (
                <Alert variant="destructive" className="mb-4">
                    <AlertDescription>{errors.file}</AlertDescription>
                </Alert>
            )}
            {importErrors.length > 0 && (
                <Alert variant="destructive" className="mb-4">
                    <AlertDescription>
                        <ul className="list-disc pl-4">
                            {importErrors.map((error, index) => (
                                <li key={index}>{error}</li>
                            ))}
                        </ul>
                    </AlertDescription>
                </Alert>
            )}
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Phone</TableHead>
                        <TableHead>Company</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {contacts.data.map((contact) => (
                        <TableRow key={contact.id}>
                            <TableCell>{contact.name}</TableCell>
                            <TableCell>{contact.email}</TableCell>
                            <TableCell>{contact.phone}</TableCell>
                            <TableCell>{contact.company}</TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>

            {(contacts.prev_page_url || contacts.next_page_url) && (
                <div className="mt-6">
                    <Pagination className="justify-between">
                        {contacts.prev_page_url ? (
                            <Button variant="outline" asChild>
                                <Link href={contacts.prev_page_url} prefetch>
                                    Previous
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="outline" disabled>
                                Previous
                            </Button>
                        )}

                        <span className="text-muted-foreground text-sm">
                            Page {contacts.current_page} of {contacts.last_page}
                        </span>

                        {contacts.next_page_url ? (
                            <Button variant="outline" asChild>
                                <Link href={contacts.next_page_url} prefetch>
                                    Next
                                </Link>
                            </Button>
                        ) : (
                            <Button variant="outline" disabled>
                                Next
                            </Button>
                        )}
                    </Pagination>
                </div>
            )}
        </AppLayout>
    );
}
