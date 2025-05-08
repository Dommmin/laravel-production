import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm, WhenVisible } from '@inertiajs/react';
import React, { useEffect, useRef, useState } from 'react';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Message {
    id: number;
    user_id: number;
    recipient_id: number;
    message: string;
    created_at: string;
}

interface ChatProps {
    users: User[];
    messages: {
        data: Message[];
    };
    currentUserId: number;
    recipient: User | null;
    messagesPagination: {
        current_page: number;
        last_page: number;
    }
}

export default function Chat({ users, messages, currentUserId, recipient, messagesPagination }: ChatProps) {
    const [selectedUser, setSelectedUser] = useState<User | null>(recipient);
    const messagesEndRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);

    const { data, setData, post, reset, processing, errors } = useForm({
        message: '',
        recipient_id: selectedUser.id,
    })

    useEffect(() => {
        if (!selectedUser.id) return;

        window.Echo.private(`chat.${currentUserId}`).listen('MessageSent', (e: any) => {
            if (
                (e.user_id === selectedUser.id && e.recipient_id === currentUserId) ||
                (e.user_id === currentUserId && e.recipient_id === selectedUser.id)
            ) {
                setMessages((prev) => [...prev, e]);
            }
        });
        return () => {
            window.Echo.leave(`chat.${currentUserId}`);
        };
    }, [selectedUser.id, currentUserId]);

    // useEffect(() => {
    //     messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    // }, [messages, selectedUser.id]);
    //
    // useEffect(() => {
    //     inputRef.current?.focus();
    // }, [selectedUser.id]);

    const changeRecipient = (user: User) => {
        setSelectedUser(user);
        setData('recipient_id', user.id);
        router.reload({
            only: ['messages'],
            data: {
                recipient_id: user.id,
            },
        });
    }

    const sendMessage = () => {
        if (!selectedUser.id || !data.message.trim()) return;
        reset('message');
        setData('recipient_id', selectedUser.id);

        post(route('chat.send'), {
            preserveScroll: true,
            onSuccess: (response) => {
                    router.reload();
                    inputRef.current?.focus();
                },
            onError: (errors) => {
                console.log(errors);
            }
        });

        // form.post(route('chat.send'), {
        //     preserveScroll: true,
        //     onSuccess: (response) => {
        //
        //     },
        //     onError: (errors) => {
        //         console.log(errors);
        //     },
        // })
    }

    // const sendMessage = async (e: React.FormEvent | React.KeyboardEvent) => {
    //     e.preventDefault?.();
    //     if (!selectedUser || !message.trim()) return;
    //     const tempMsg: Message = {
    //         id: Date.now(), // tymczasowe id
    //         user_id: currentUserId,
    //         recipient_id: selectedUser.id,
    //         message,
    //         created_at: new Date().toISOString(),
    //     };
    //     setMessages((prev) => [...prev, tempMsg]); // optimistic update
    //     setMessage('');
    // };
    //
    const handleInputKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            sendMessage();
        }
    };

    const loadMore = () => {
        router.reload({
            only: ['messages', 'messagesPagination'],
            data: {
                page: messagesPagination.current_page + 1,
            }
        })
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Chat', href: '/chat' }]}>
            <Head title="Chat" />
            <div className="flex h-[80vh] overflow-hidden rounded border bg-background shadow dark:border-zinc-800">
                <aside className="w-1/4 border-r bg-muted p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <h2 className="mb-4 text-lg font-bold text-foreground">Użytkownicy</h2>
                    <ul className="space-y-1">
                        {users.map((user) => (
                            <li key={user.id}>
                                <button
                                    className={`w-full rounded px-2 py-1 text-left transition-colors focus:outline-none focus:ring-2 focus:ring-primary/50 ${selectedUser.id === user.id ? 'bg-primary/10 font-bold text-primary dark:bg-primary/20' : 'hover:bg-accent/50'}`}
                                    onClick={() => changeRecipient(user)}
                                    tabIndex={0}
                                >
                                    {user.name}
                                </button>
                            </li>
                        ))}
                    </ul>
                </aside>
                <main className="flex flex-1 flex-col bg-background">
                    <div className="flex-1 space-y-2 overflow-y-auto bg-background p-4">
                        {/*<Button onClick={loadMore} className="mb-2" variant="link" disabled={messagesPagination.current_page === messagesPagination.last_page}>Load more</Button>*/}
                        {messages
                            .filter(
                                (msg) =>
                                    (msg.user_id === currentUserId && msg.recipient_id === selectedUser.id) ||
                                    (msg.user_id === selectedUser.id && msg.recipient_id === currentUserId),
                            )
                            .map((msg) => (
                                <div
                                    key={msg.id}
                                    className={`max-w-[70%] rounded-lg px-4 py-2 text-sm shadow transition-all duration-200 ${msg.user_id === currentUserId ? 'ml-auto bg-primary text-primary-foreground' : 'mr-auto border bg-card text-card-foreground dark:border-zinc-800'}`}
                                >
                                    <div>{msg.message}</div>
                                    <div className="mt-1 text-right text-xs text-muted-foreground">{new Date(msg.created_at).toLocaleTimeString()}</div>
                                </div>
                            ))}
                        <WhenVisible
                            always
                            fallback={() => <div>Loading...</div>}
                            params={{
                                data: {
                                    page: messagesPagination.current_page + 1,
                                },
                                only: ['messages', 'messagesPagination'],
                            }}
                        >
                            {messagesPagination.current_page >= messagesPagination.last_page && (
                                <div>You have reached the end.</div>
                            )}
                        </WhenVisible>
                        <div ref={messagesEndRef} />
                    </div>
                    {recipient && (
                        <div className="flex border-t bg-background p-4 dark:border-zinc-800">
                            <Input
                                ref={inputRef}
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                                onKeyDown={handleInputKeyDown}
                                placeholder={'Wiadomość do ' + selectedUser.name}
                                className="mr-2 flex-1"
                                autoComplete="off"
                                disabled={!selectedUser}
                            />
                            <Button type="button" onClick={sendMessage} className="shrink" variant="default">
                                Wyślij
                            </Button>
                        </div>
                    )}
                </main>
            </div>
        </AppLayout>
    );
}
