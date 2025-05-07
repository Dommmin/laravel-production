import { Article } from '@/types';
import { Link } from '@inertiajs/react';
import { route } from 'ziggy-js';

export function ArticlesTable({ articles }: { articles: Article[] }) {
    if (articles.length === 0) {
        return <div className="bg-background text-muted-foreground rounded-lg border px-4 py-6 text-center shadow">No results found</div>;
    }

    return (
        <div className="bg-background overflow-x-auto rounded-lg border shadow">
            <table className="divide-border min-w-full divide-y">
                <thead className="bg-muted">
                    <tr>
                        {['Title', 'Author', 'Tags', 'City'].map((col) => (
                            <th key={col} className="px-4 py-2 text-left text-xs font-semibold tracking-wider uppercase">
                                {col}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-border divide-y">
                    {articles.map((a) => (
                        <tr key={a.slug} className="hover:bg-accent transition">
                            <td className="px-4 py-2">
                                <Link href={route('articles.show', a.slug)} className="hover:underline">
                                    {a.title}
                                </Link>
                            </td>
                            <td className="px-4 py-2">{typeof a.user === 'object' && a.user?.name}</td>
                            <td className="px-4 py-2">{Array.isArray(a.tags) ? a.tags.join(', ') : ''}</td>
                            <td className="px-4 py-2">{a.city_name ?? ''}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
