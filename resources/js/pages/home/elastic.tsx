import { ArticlesTable } from '@/components/home/articles-table';
import { PaginationControls } from '@/components/home/pagination-controls';
import { SearchFilters } from '@/components/home/search-filters';
import AppLayout from '@/layouts/app-layout';
import type { Article, BreadcrumbItem, FiltersProps } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Home', href: '/' }];

const PAGE_SIZE = 20;

interface HomeProps {
    articles: Article[];
    total: number;
    filters: FiltersProps;
    cities: string[];
    tags: string[];
}

export default function Home({ articles, total, filters, cities, tags }: HomeProps) {
    const [query, setQuery] = useState(filters.q || '');
    const [tag, setTag] = useState(filters.tag || '');
    const [city, setCity] = useState(filters.city || '');
    const [radius, setRadius] = useState(filters.radius || 0);
    const [lat, setLat] = useState(filters.lat || '');
    const [lon, setLon] = useState(filters.lon || '');
    const [page, setPage] = useState(Number(filters.page) || 1);

    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get('/', { q: query, tag, city, radius, lat, lon, page: 1 }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(timeout);
    }, [query, tag, city, radius, lat, lon]);

    useEffect(() => {
        if (city) {
            const found = articles.find((a) => a.city_name === city);
            if (found?.location) {
                setLat(found.location.lat.toString());
                setLon(found.location.lon.toString());
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
            <Head title="Index" />
            <div className="flex flex-col gap-6 p-4">
                <SearchFilters
                    q={query}
                    setQuery={setQuery}
                    tag={tag}
                    setTag={setTag}
                    tags={tags}
                    city={city}
                    setCity={setCity}
                    cities={cities}
                    radius={radius}
                    setRadius={setRadius}
                    lat={lat}
                    lon={lon}
                    page={page}
                />

                <ArticlesTable articles={articles} />

                {totalPages > 1 && <PaginationControls page={page} totalPages={totalPages} onChange={handlePageChange} />}
            </div>
        </AppLayout>
    );
}
