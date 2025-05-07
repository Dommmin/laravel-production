import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type Article, type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];

const PAGE_SIZE = 20;

interface Filters {
    q?: string;
    tag?: string;
    city?: string;
    radius?: number;
    lat?: number;
    lon?: number;
    page?: number;
}

export default function Home({
    articles,
    total,
    filters,
    cities,
    tags,
}: {
    articles: Article[];
    total: number;
    filters: Filters;
    cities: string[];
    tags: string[];
}) {
    const [query, setQuery] = useState(filters.q || '');
    const [tag, setTag] = useState(filters.tag || '');
    const [city, setCity] = useState(filters.city || '');
    const [radius, setRadius] = useState(filters.radius || 0);
    const [lat, setLat] = useState(filters.lat || '');
    const [lon, setLon] = useState(filters.lon || '');
    const [page, setPage] = useState(Number(filters.page) || 1);

    // Instant Search
    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get('/', { q: query, tag, city, radius, lat, lon, page: 1 }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(timeout);
    }, [query, tag, city, radius, lat, lon]);

    useEffect(() => {
        if (city) {
            const found = articles.find((a) => a.city_name === city);
            if (found && found.location) {
                setLat(found.location.lat);
                setLon(found.location.lon);
            }
        } else {
            setLat('');
            setLon('');
        }
    }, [articles, city]);

    const handlePageChange = (newPage: number) => {
        setPage(newPage);
        router.get('/', { q: query, tag, city, radius, lat, lon, page: newPage }, { preserveState: true, replace: true });
    };

    const totalPages = Math.ceil(total / PAGE_SIZE);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Home" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col gap-4 md:flex-row md:items-end">
                    <Input
                        placeholder="Search..."
                        value={query}
                        onChange={(e) => {
                            setQuery(e.target.value);
                            setPage(1);
                        }}
                        className="w-full md:w-1/4"
                    />
                    <Select
                        value={tag}
                        onValueChange={(v) => {
                            setTag(v);
                            setPage(1);
                        }}
                    >
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Any tag" />
                        </SelectTrigger>
                        <SelectContent>
                            {tags.map((t) => (
                                <SelectItem key={t} value={t}>
                                    {t}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={city}
                        onValueChange={(v) => {
                            setCity(v);
                            setPage(1);
                        }}
                    >
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Any city" />
                        </SelectTrigger>
                        <SelectContent>
                            {cities.map((c) => (
                                <SelectItem key={c} value={c}>
                                    {c}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select
                        value={String(radius)}
                        onValueChange={(v) => {
                            setRadius(Number(v));
                            setPage(1);
                        }}
                    >
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Radius (km)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">Any Distance</SelectItem>
                            <SelectItem value="10">10 km</SelectItem>
                            <SelectItem value="50">50 km</SelectItem>
                            <SelectItem value="100">100 km</SelectItem>
                            <SelectItem value="300">300 km</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button variant="destructive" onClick={() => router.get('/')}>
                        Clear filters
                    </Button>
                </div>
                <div className="bg-background overflow-x-auto rounded-lg border shadow">
                    <table className="divide-border min-w-full divide-y">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs font-semibold tracking-wider uppercase">Title</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold tracking-wider uppercase">Author</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold tracking-wider uppercase">Tags</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold tracking-wider uppercase">City</th>
                            </tr>
                        </thead>
                        <tbody className="divide-border divide-y">
                            {articles.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="text-muted-foreground px-4 py-6 text-center">
                                        No results found
                                    </td>
                                </tr>
                            )}
                            {articles.map((a, idx) => (
                                <tr key={idx} className="hover:bg-accent transition">
                                    <td className="px-4 py-2">
                                        <Link href={route('articles.show', a.slug)} className="flex items-center hover:underline">
                                            {a.title}
                                        </Link>
                                    </td>
                                    <td className="px-4 py-2">
                                        {typeof a.user === 'object' && a.user && 'name' in a.user ? a.user.name : (a.user ?? '')}
                                    </td>
                                    <td className="px-4 py-2">{Array.isArray(a.tags) ? a.tags.join(', ') : ''}</td>
                                    <td className="px-4 py-2">{a.city_name ?? ''}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {/* PAGINACJA */}
                {totalPages > 1 && (
                    <div className="mt-4 flex items-center justify-center gap-2">
                        <Button variant="outline" size="sm" onClick={() => handlePageChange(page - 1)} disabled={page === 1}>
                            Previous
                        </Button>
                        <span className="text-sm">
                            Page <span className="font-semibold">{page}</span> of <span className="font-semibold">{totalPages}</span>
                        </span>
                        <Button variant="outline" size="sm" onClick={() => handlePageChange(page + 1)} disabled={page === totalPages}>
                            Next
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
