import { UserList } from '@/components/chat/users-list';
import AppLayout from '@/layouts/app-layout';
import type { User } from '@/types';
import { Head, router } from '@inertiajs/react';
import React from 'react';

interface ChatIndexProps {
    users: User[];
    currentUserId: number;
}

export default function Chat({ users, currentUserId }: ChatIndexProps) {
    const handleSelectUser = (user: User) => {
        router.post(route('chat.findOrCreate', user.id));
    };
    return (
        <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}> 
            <Head title="Chat" />
            <div className="bg-background flex h-[80vh] overflow-hidden rounded border shadow dark:border-zinc-800">
                <UserList users={users} selectedUserId={0} onSelectUser={handleSelectUser} />
                <div className="bg-background flex flex-1 items-center justify-center">
                    <p className="text-muted-foreground">Wybierz użytkownika, aby rozpocząć czat</p>
                </div>
            </div>
        </AppLayout>
    );
}
