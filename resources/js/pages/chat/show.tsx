import { MessageList } from '@/components/chat/message-list';
import { UserList } from '@/components/chat/users-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import type { Message, User, Chat } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import React, { useEffect, useRef } from 'react';

interface ChatShowProps {
    chat: Chat;
    users: User[];
    currentUserId: number;
    messages: Message[];
    messagesPagination: {
        current_page: number;
        last_page: number;
    };
}

export default function ChatShow({ chat, users, currentUserId, messages, messagesPagination }: ChatShowProps) {
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);
    const { data, setData, post, reset, processing } = useForm({
        message: '',
    });

    useEffect(() => {
        inputRef.current?.focus();
    }, [chat.id]);

    useEffect(() => {
        if (messages.length > 0) {
            messagesEndRef.current?.scrollIntoView({ behavior: 'auto' });
        }
    }, [messages]);

    const sendMessage = () => {
        if (!data.message.trim()) return;
        post(route('chat.send', chat.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset('message');
                inputRef.current?.focus();
            },
        });
    };

    const handleInputKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    };

    // Funkcja do ładowania kolejnych stron bez page/recipient_id w URL
    const loadMoreMessages = () => {
        if (messagesPagination.current_page >= messagesPagination.last_page) return;
        router.reload({
            only: ['messages', 'messagesPagination'],
            data: {}, // nie przekazujemy page ani recipient_id
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}> 
            <Head title={chat.name || 'Chat'} />
            <div className="bg-background flex h-[80vh] overflow-hidden rounded border shadow dark:border-zinc-800">
                <UserList users={users} selectedUserId={currentUserId} onSelectUser={() => router.visit('/chat')} />
                <main className="bg-background flex flex-1 flex-col">
                    <div className="bg-background flex-1 space-y-2 overflow-y-auto p-4">
                        <MessageList
                            messages={messages}
                            currentUserId={currentUserId}
                            selectedUserId={currentUserId}
                            pagination={messagesPagination}
                            onLoadMore={loadMoreMessages}
                            messagesEndRef={messagesEndRef as React.RefObject<HTMLDivElement>}
                        />
                    </div>
                    <div className="bg-background flex border-t p-4 dark:border-zinc-800">
                        <Input
                            ref={inputRef}
                            value={data.message}
                            onChange={(e) => setData('message', e.target.value)}
                            onKeyDown={handleInputKeyDown}
                            placeholder="Napisz wiadomość..."
                            className="mr-2 flex-1"
                            autoComplete="off"
                            disabled={processing}
                        />
                        <Button
                            type="button"
                            onClick={sendMessage}
                            className="shrink"
                            variant="default"
                            disabled={processing || !data.message.trim()}
                        >
                            Wyślij
                        </Button>
                    </div>
                </main>
            </div>
        </AppLayout>
    );
} 