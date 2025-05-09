import { Button } from '@/components/ui/button';
import { type Message } from '@/types';
import { WhenVisible } from '@inertiajs/react';
import React from 'react';

interface MessageListProps {
    messages: Message[];
    currentUserId: number;
    selectedUserId: number;
    pagination: {
        current_page: number;
        last_page: number;
    };
    onLoadMore: () => void;
    messagesEndRef: React.RefObject<HTMLDivElement>;
}

export const MessageList: React.FC<MessageListProps> = ({ messages, currentUserId, selectedUserId, pagination, onLoadMore, messagesEndRef }) => {
    // Sortuj rosnąco po created_at (najstarsze na górze, najnowsze na dole)
    const conversationMessages = messages
        .filter(
            (msg) =>
                (msg.user_id === currentUserId && msg.recipient_id === selectedUserId) ||
                (msg.user_id === selectedUserId && msg.recipient_id === currentUserId),
        )
        .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());

    const hasMoreMessages = pagination.current_page < pagination.last_page;

    return (
        <div className="bg-background flex-1 space-y-2 overflow-y-auto p-4">
            {hasMoreMessages && (
                <div className="text-center">
                    <Button onClick={onLoadMore} className="mb-2" variant="outline" size="sm">
                        Load more messages
                    </Button>
                </div>
            )}
            <WhenVisible
                fallback="Loading older messages..."
                params={{
                    data: {
                        recipient_id: selectedUserId,
                        page: pagination.current_page + 1,
                    },
                    only: ['messages', 'messagesPagination'],
                }}
            >
                {/* pusty children, wymagany przez typy */}
                <></>
            </WhenVisible>

            {conversationMessages.length === 0 ? (
                <div className="flex h-full items-center justify-center">
                    <p className="text-muted-foreground">No messages yet. Start a conversation!</p>
                </div>
            ) : (
                conversationMessages.map((msg) => (
                    <div
                        key={msg.id}
                        data-message-id={msg.id}
                        className={`max-w-[70%] rounded-lg px-4 py-2 text-sm shadow transition-all duration-200 ${
                            msg.user_id === currentUserId
                                ? 'bg-primary text-primary-foreground ml-auto'
                                : 'bg-card text-card-foreground mr-auto border dark:border-zinc-800'
                        }`}
                    >
                        <div className="break-words">{msg.message}</div>
                        <div className="text-muted-foreground mt-1 text-right text-xs">{new Date(msg.created_at).toLocaleTimeString()}</div>
                    </div>
                ))
            )}
            <div ref={messagesEndRef} />
        </div>
    );
};
