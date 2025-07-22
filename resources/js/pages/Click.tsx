import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { router } from '@inertiajs/react';
import { useEffect } from 'react';

interface ClickProps {
    id: number;
    times: number;
}

export default function Click({ click }: { click: ClickProps }) {
    useEffect(() => {
        const channel = window.Echo.channel('clicks');

        channel.listen('.Clicked', () => {
            console.log('Click event');
            router.reload({ only: ['click'] });
        });

        return () => {
            window.Echo.leave('clicks');
        };
    }, []);

    const handleClick = () => {
        router.post(route('clicks.click'));
    };

    const handleReset = () => {
        router.post(route('clicks.reset'));
    };

    return (
        <AppLayout>
            <div className="flex min-h-screen items-center justify-center">
                <Button onClick={handleClick}>Clicked {click?.times || 0} times</Button>
                <Button onClick={handleReset}>Reset</Button>
            </div>
        </AppLayout>
    );
}
