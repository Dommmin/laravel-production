import type { User } from '@/types';
import React from 'react';

interface UserListProps {
    users: User[];
    selectedUserId: number;
    onSelectUser: (user: User) => void;
}

export const UserList: React.FC<UserListProps> = ({ users, selectedUserId, onSelectUser }) => {
    return (
        <aside className="bg-muted w-1/4 border-r p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 className="text-foreground mb-4 text-lg font-bold">Users</h2>
            <ul className="space-y-1">
                {users.map((user) => (
                    <li key={user.id}>
                        <button
                            className={`focus:ring-primary/50 w-full rounded px-2 py-1 text-left transition-colors focus:ring-2 focus:outline-none ${
                                selectedUserId === user.id ? 'bg-primary/10 text-primary dark:bg-primary/20 font-bold' : 'hover:bg-accent/50'
                            }`}
                            onClick={() => onSelectUser(user)}
                            tabIndex={0}
                        >
                            {user.name}
                        </button>
                    </li>
                ))}
            </ul>
        </aside>
    );
};
