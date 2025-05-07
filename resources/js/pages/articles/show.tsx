import { Card, CardContent, CardHeader } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type Article } from '@/types';
import { Head } from '@inertiajs/react';

export default function Show({ article }: { article: Article }) {
    return (
        <AppLayout>
            <Head title={article.title} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <h1 className="mb-4 text-2xl font-bold">{article.title}</h1>
                        </CardHeader>
                        <CardContent>
                            <p className="mb-4 text-gray-700">{article.content}</p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
