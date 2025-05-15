import { UserList } from '@/components/chat/users-list';
import AppLayout from '@/layouts/app-layout';
import type { Chat, User } from '@/types';
import { Head, router } from '@inertiajs/react';

interface ChatIndexProps {
    users: User[];
    chats: Chat[];
}

export default function Chat({ users, chats }: ChatIndexProps) {
    const handleSelectUser = (user: User) => {
        router.post(route('chat.findOrCreate', user.id));
    };
    return (
        <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}>
            <Head title="Chat" />
            <div className="bg-background flex h-[80vh] overflow-hidden rounded border shadow dark:border-zinc-800">
                <UserList chats={chats} />
                <div className="bg-background flex flex-1 items-center justify-center">
                    <p className="text-muted-foreground">Wybierz użytkownika, aby rozpocząć czat</p>
                </div>
            </div>
        </AppLayout>
    );
}
