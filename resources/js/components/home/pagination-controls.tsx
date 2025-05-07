import { Button } from '@/components/ui/button';

export function PaginationControls({ page, totalPages, onChange }: { page: number; totalPages: number; onChange: (page: number) => void }) {
    return (
        <div className="mt-4 flex items-center justify-center gap-2">
            <Button variant="outline" size="sm" onClick={() => onChange(page - 1)} disabled={page === 1}>
                Previous
            </Button>
            <span className="text-sm">
                Page <span className="font-semibold">{page}</span> of <span className="font-semibold">{totalPages}</span>
            </span>
            <Button variant="outline" size="sm" onClick={() => onChange(page + 1)} disabled={page === totalPages}>
                Next
            </Button>
        </div>
    );
}
