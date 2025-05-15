import type { Chat } from '@/types';
import { Link } from '@inertiajs/react';
import React from 'react';

interface UserListProps {
    chats: Chat[];
}

export const UserList: React.FC<UserListProps> = ({ chats }) => {
    return (
        <aside className="bg-muted w-1/4 border-r p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 className="text-foreground mb-4 text-lg font-bold">Users</h2>
            <ul className="space-y-1">
                {chats.map((chat) => (
                    <li key={chat.id}>
                        <Link
                            prefetch
                            href={route('chat.show', chat.id)}
                            className={`focus:ring-primary/50 w-full rounded px-2 py-1 text-left transition-colors focus:ring-2 focus:outline-none ${'bg-primary/10 text-primary dark:bg-primary/20 font-bold'}`}
                            tabIndex={0}
                        >
                            {chat.name}
                        </Link>
                    </li>
                ))}
            </ul>
        </aside>
    );
};
