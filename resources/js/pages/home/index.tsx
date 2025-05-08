import { ArticlesTable } from '@/components/home/articles-table';
import { SearchFilters } from '@/components/home/search-filters';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/ui/pagination';
import AppLayout from '@/layouts/app-layout';
import type { Article, BreadcrumbItem, FiltersProps } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Home', href: '/' }];

interface HomeProps {
    articles: {
        data: Article[];
        total: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        current_page: number;
        from: number;
        to: number;
    };
    filters: FiltersProps;
    cities: string[];
    tags: string[];
}

export default function Home({ articles, filters, cities, tags }: HomeProps) {
    const [query, setQuery] = useState(filters.q || '');
    const [tag, setTag] = useState(filters.tag || '');
    const [city, setCity] = useState(filters.city || '');
    const [radius, setRadius] = useState(filters.radius || 0);
    const [lat, setLat] = useState(filters.lat || '');
    const [lon, setLon] = useState(filters.lon || '');
    const [page] = useState(Number(filters.page) || 1);

    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get('/', { q: query, tag, city, radius, lat, lon, page }, { preserveState: true, replace: true });
        }, 400);
        return () => clearTimeout(timeout);
    }, [query, tag, city, radius, lat, lon, page]);

    useEffect(() => {
        if (city) {
            const found = articles.data.find((a) => a.city_name === city);
            if (found?.location) {
                setLat(found.location.lat.toString());
                setLon(found.location.lon.toString());
            }
        } else {
            setLat('');
            setLon('');
        }
    }, [articles, city]);

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

                <ArticlesTable articles={articles.data} />

                {(articles.prev_page_url || articles.next_page_url) && (
                    <div className="mt-6">
                        <Pagination className="justify-between">
                            {articles.prev_page_url ? (
                                <Button variant="outline" asChild>
                                    <Link href={articles.prev_page_url}>Previous</Link>
                                </Button>
                            ) : (
                                <Button variant="outline" disabled>
                                    Previous
                                </Button>
                            )}

                            <span className="text-muted-foreground text-sm">
                                Page {articles.current_page} of {articles.last_page}
                            </span>

                            {articles.next_page_url ? (
                                <Button variant="outline" asChild>
                                    <Link href={articles.next_page_url}>Next</Link>
                                </Button>
                            ) : (
                                <Button variant="outline" disabled>
                                    Next
                                </Button>
                            )}
                        </Pagination>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
