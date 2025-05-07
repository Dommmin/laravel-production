import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FiltersProps } from '@/types';
import { router } from '@inertiajs/react';

export function SearchFilters({ q, setQuery, tag, setTag, city, setCity, radius, setRadius, cities, tags }: FiltersProps) {
    return (
        <div className="flex flex-col gap-4 md:flex-row md:items-end">
            <Input placeholder="Search..." value={q} onChange={(e) => setQuery(e.target.value)} className="w-full md:w-1/4" />
            <Select value={tag} onValueChange={(v) => setTag(v)}>
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
            <Select value={city} onValueChange={(v) => setCity(v)}>
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
            <Select value={String(radius)} onValueChange={(v) => setRadius(Number(v))}>
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
    );
}
