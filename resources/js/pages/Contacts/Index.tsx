import React, { useEffect } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Loader2 } from 'lucide-react';
import Echo from 'laravel-echo';

interface Contact {
  id: number;
  name: string;
  email: string;
  phone?: string;
  company?: string;
}

interface ContactData {
    data: Contact[],
}

export default function ContactsIndex({ contacts }: { contacts: ContactData }) {
    const { setData, post, processing, errors } = useForm({
        file: null,
    })
  const [importing, setImporting] = React.useState(false);
  const page = usePage();

  useEffect(() => {
    window.Echo.channel('contacts-import').listen('.ContactImportFinished', () => {
      setImporting(false);
      router.reload({ only: ['contacts'] });
    });
    return () => {
      window.Echo.leave('contacts-import');
    };
  }, []);

  const handleImport = (event: React.FormEvent) => {
    event.preventDefault();

    post(route('contacts.import'), {
        onSuccess: () => {
            setImporting(true);
        },
    });
  };

  const handleExport = () => {
    window.location.href = '/contacts/export';
  };

  return (
    <AppLayout>
      <Head title="Contacts" />
      <h1 className="text-2xl font-bold mb-4">Contacts</h1>
      {page.props.success && <div className="mb-4 p-2 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{page.props.success}</div>}
      {page.props.error && <div className="mb-4 p-2 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{page.props.error}</div>}
      <form onSubmit={handleImport} className="mb-6 flex items-center gap-2">
        <Input
          type="file"
          accept=".csv,.xlsx"
          onChange={e => setData('file', e.target.files[0])}
          className="max-w-xs"
        />
        <Button type="submit" disabled={processing} variant="default">Import</Button>
        <Button type="button" onClick={handleExport} variant="secondary">Export</Button>
        <a href="/js/pages/Contacts/SampleImport.csv" download className="ml-2 underline text-sm">Download sample CSV</a>
      </form>
      {importing && (
        <div className="flex items-center gap-2 mb-4">
          <Loader2 className="animate-spin" />
          <span>Import in progress... Please wait.</span>
        </div>
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
          {contacts.data.map(contact => (
            <TableRow key={contact.id}>
              <TableCell>{contact.name}</TableCell>
              <TableCell>{contact.email}</TableCell>
              <TableCell>{contact.phone}</TableCell>
              <TableCell>{contact.company}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </AppLayout>
  );
}
