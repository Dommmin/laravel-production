import { Head, router } from '@inertiajs/react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import React, { useEffect, useState } from 'react';
import { Select, SelectTrigger, SelectContent, SelectItem, SelectValue } from '@/components/ui/select';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const PAGE_SIZE = 20;

interface Article {
    id: number;
    title: string;
    body: string;
    user: string | { name: string };
    tags: string[];
    created_at: string;
    updated_at: string;
    location?: {
        lat: number;
        lon: number;
    };
    location_name?: string;
    city_name?: string;
}

interface Filters {
    q?: string;
    tag?: string;
    city?: string;
    radius?: number;
    lat?: number;
    lon?: number;
    page?: number;
}

export default function Dashboard({ articles, total, filters, cities, tags } : { articles: Article[]; total: number; filters: Filters; cities: string[]; tags: string[] }) {
    const [query, setQuery] = useState(filters.q || '');
    const [tag, setTag] = useState(filters.tag || '');
    const [city, setCity] = useState(filters.city || '');
    const [radius, setRadius] = useState(filters.radius || 0);
    const [lat, setLat] = useState(filters.lat || '');
    const [lon, setLon] = useState(filters.lon || '');
    const [page, setPage] = useState(Number(filters.page) || 1);

    // Obsługa instant search
    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get(
                '/dashboard',
                { q: query, tag, city, radius, lat, lon, page: 1 },
                { preserveState: true, replace: true }
            );
        }, 400);
        return () => clearTimeout(timeout);
    }, [query, tag, city, radius, lat, lon]);

    // Obsługa wyboru miasta (ustaw lat/lon jeśli znajdziesz w articles)
    useEffect(() => {
        if (city) {
            const found = articles.find(a => a.city_name === city);
            if (found && found.location) {
                setLat(found.location.lat);
                setLon(found.location.lon);
            }
        } else {
            setLat('');
            setLon('');
        }
    }, [articles, city]);

    // Obsługa paginacji
    const handlePageChange = (newPage: number) => {
        setPage(newPage);
        router.get(
            '/dashboard',
            { q: query, tag, city, radius, lat, lon, page: newPage },
            { preserveState: true, replace: true }
        );
    };

    const totalPages = Math.ceil(total / PAGE_SIZE);

    console.log(tags);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col md:flex-row md:items-end gap-4">
                    <Input
                        placeholder="Szukaj..."
                        value={query}
                        onChange={e => { setQuery(e.target.value); setPage(1); }}
                        className="w-full md:w-1/4"
                    />
                    <Select value={tag} onValueChange={v => { setTag(v); setPage(1); }}>
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Wszystkie tagi" />
                        </SelectTrigger>
                        <SelectContent>
                            {tags.map((t) => (
                                <SelectItem key={t} value={t}>{t}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={city} onValueChange={v => { setCity(v); setPage(1); }}>
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Wszystkie miasta" />
                        </SelectTrigger>
                        <SelectContent>
                            {cities.map((c) => (
                                <SelectItem key={c} value={c}>{c}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={String(radius)} onValueChange={v => { setRadius(Number(v)); setPage(1); }}>
                        <SelectTrigger className="w-full md:w-1/4">
                            <SelectValue placeholder="Promień (km)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="0">Dowolny</SelectItem>
                            <SelectItem value="10">10 km</SelectItem>
                            <SelectItem value="50">50 km</SelectItem>
                            <SelectItem value="100">100 km</SelectItem>
                            <SelectItem value="300">300 km</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="overflow-x-auto rounded-lg border bg-background shadow">
                    <table className="min-w-full divide-y divide-border">
                        <thead className="bg-muted">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider">Tytuł</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider">Autor</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider">Tagi</th>
                                <th className="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider">Miasto</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border">
                            {articles.length === 0 && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-6 text-center text-muted-foreground">
                                        Brak wyników
                                    </td>
                                </tr>
                            )}
                            {articles.map((a, idx) => (
                                <tr key={idx} className="hover:bg-accent transition">
                                    <td className="px-4 py-2">{a.title}</td>
                                    <td className="px-4 py-2">{
                                        typeof a.user === 'object' && a.user && 'name' in a.user
                                            ? a.user.name
                                            : (a.user ?? '')
                                    }</td>
                                    <td className="px-4 py-2">{Array.isArray(a.tags) ? a.tags.join(', ') : ''}</td>
                                    <td className="px-4 py-2">{a.city_name ?? ''}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {/* PAGINACJA */}
                {totalPages > 1 && (
                    <div className="flex justify-center items-center gap-2 mt-4">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(page - 1)}
                            disabled={page === 1}
                        >
                            Poprzednia
                        </Button>
                        <span className="text-sm">
                            Strona <span className="font-semibold">{page}</span> z <span className="font-semibold">{totalPages}</span>
                        </span>
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handlePageChange(page + 1)}
                            disabled={page === totalPages}
                        >
                            Następna
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
