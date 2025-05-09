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
    // Sort ascending by created_at (oldest at the top, newest at the bottom)
    const conversationMessages = messages
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
                }}
            >
                {/* empty children, required by types */}
                <></>
            </WhenVisible>

            {conversationMessages.length === 0 ? (
                <div className="flex h-full items-center justify-center">
                    <p className="text-muted-foreground">No messages yet. Start a conversation!</p>
                </div>
            ) : (
                conversationMessages.map((msg, idx) => {
                    const isMine = msg.user_id === currentUserId;
                    const isLastRead = msg.read_by.includes(currentUserId) &&
                        (idx === conversationMessages.length - 1 || !conversationMessages[idx + 1].read_by.includes(currentUserId));
                    return (
                        <div
                            key={msg.id}
                            data-message-id={msg.id}
                            className={`max-w-[70%] rounded-lg px-4 py-2 text-sm shadow transition-all duration-200 flex items-end gap-2 ${
                                isMine
                                    ? 'bg-primary text-primary-foreground ml-auto flex-row-reverse'
                                    : 'bg-card text-card-foreground mr-auto border dark:border-zinc-800'
                            }`}
                        >
                            {msg.user.avatar && (
                                <img src={msg.user.avatar} alt={msg.user.name} className="w-7 h-7 rounded-full object-cover border" />
                            )}
                            <div className="flex-1">
                                <div className="break-words">{msg.message}</div>
                                <div className="text-muted-foreground mt-1 text-right text-xs flex items-center gap-1">
                                    {new Date(msg.created_at).toLocaleTimeString()}
                                    {isLastRead && (
                                        <span title="Przeczytane" className="ml-1 text-blue-500">✔✔</span>
                                    )}
                                </div>
                            </div>
                        </div>
                    );
                })
            )}
            <div ref={messagesEndRef} />
        </div>
    );
};
