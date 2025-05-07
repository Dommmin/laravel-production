import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';

interface UploadedFile {
    id: number;
    name: string;
    url: string;
}

export default function Index({ files }: { files: UploadedFile[] }) {
    const form = useForm<{ file: File | null }>({
        file: null,
    });

    const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            form.setData('file', file);
        }
    };

    const handleFileUpload = () => {
        form.post('/files');
    };

    return (
        <AppLayout>
            <Head title="Files" />

            <div className="container mx-auto">
                <div>
                    <Input onChange={handleFileChange} type="file" className="mb-4" />
                    <Button onClick={handleFileUpload} className="rounded bg-blue-500 px-4 py-2 text-white">
                        Upload
                    </Button>
                </div>
                <h1 className="text-2xl font-bold">Files</h1>
                <p className="mt-4">List of files will be displayed here.</p>
                <div className="mb-4 flex flex-wrap items-center gap-4">
                    {files.map((file) => (
                        <img key={file.id} src={file.url} alt={file.name} width="100" className="rounded-2xl" />
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
