import { MessageList } from '@/components/chat/message-list';
import { UserList } from '@/components/chat/users-list';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useChatRealtime } from '@/hooks/use-chat-realtime';
import AppLayout from '@/layouts/app-layout';
import type { Message, User } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

interface ChatProps {
    users: User[];
    messages: Message[];
    currentUserId: number;
    recipient: User | null;
    messagesPagination: {
        current_page: number;
        last_page: number;
    };
}

export default function Chat({ users, messages: initialMessages, currentUserId, recipient: initialRecipient, messagesPagination }: ChatProps) {
    const selectedUser = initialRecipient;
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);
    const [initial, setInitial] = useState(true);
    const messagesContainerRef = useRef<HTMLDivElement>(null);

    const { messages } = useChatRealtime({
        initialMessages: initialMessages,
        currentUserId,
        selectedUserId: selectedUser?.id,
    });

    const { data, setData, post, reset, processing } = useForm({
        message: '',
        recipient_id: selectedUser?.id || 0,
    });

    useEffect(() => {
        inputRef.current?.focus();
    }, [selectedUser?.id]);

    useEffect(() => {
        if (messages.length > 0) {
            messagesEndRef.current?.scrollIntoView({ behavior: 'auto' });
        }
    }, [messages]);

    const changeRecipient = (user: User) => {
        router.visit(route('chat.index'), {
            data: { recipient_id: user.id },
            preserveState: true,
            only: ['messages', 'messagesPagination', 'recipient'],
        });
    };

    const sendMessage = () => {
        if (!selectedUser?.id || !data.message.trim()) return;
        setData('recipient_id', selectedUser.id);
        post(route('chat.send'), {
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

    // Helper function to find the id of the first visible message
    const getFirstVisibleMessageId = () => {
        const container = messagesContainerRef.current;
        if (!container) return null;
        const messageNodes = Array.from(container.querySelectorAll('[data-message-id]')) as HTMLElement[];
        for (const node of messageNodes) {
            const rect = node.getBoundingClientRect();
            if (rect.top >= container.getBoundingClientRect().top) {
                return node.dataset.messageId;
            }
        }
        return null;
    };

    const scrollToMessageId = (id: string | null) => {
        if (!id) return;
        const container = messagesContainerRef.current;
        const node = container?.querySelector(`[data-message-id="${id}"]`);
        if (node && container) {
            container.scrollTop = (node as HTMLElement).offsetTop - container.offsetTop;
        }
    };

    const loadMoreMessages = () => {
        if (messagesPagination.current_page >= messagesPagination.last_page) return;
        const firstVisibleId = getFirstVisibleMessageId();
        router.reload({
            only: ['messages', 'messagesPagination'],
            data: {
                recipient_id: selectedUser?.id,
                page: messagesPagination.current_page + 1,
            },
            onSuccess: () => {
                setTimeout(() => scrollToMessageId(firstVisibleId ?? null), 0);
            },
        });
    };

    if (!selectedUser) {
        return (
            <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}>
                <Head title="Chat" />
                <div className="bg-background flex h-[80vh] overflow-hidden rounded border shadow dark:border-zinc-800">
                    <UserList users={users as import('@/types').User[]} selectedUserId={0} onSelectUser={changeRecipient} />
                    <div className="bg-background flex flex-1 items-center justify-center">
                        <p className="text-muted-foreground">Select a user to start chatting</p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}>
            <Head title="Chat" />
            <div className="bg-background flex h-[80vh] overflow-hidden rounded border shadow dark:border-zinc-800">
                <UserList users={users as import('@/types').User[]} selectedUserId={selectedUser.id} onSelectUser={changeRecipient} />
                <main className="bg-background flex flex-1 flex-col">
                    <div ref={messagesContainerRef} className="bg-background flex-1 space-y-2 overflow-y-auto p-4">
                        <MessageList
                            messages={messages}
                            currentUserId={currentUserId}
                            selectedUserId={selectedUser.id}
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
                            placeholder={`Message do ${selectedUser.name}`}
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
                            Send
                        </Button>
                    </div>
                </main>
            </div>
        </AppLayout>
    );
}
