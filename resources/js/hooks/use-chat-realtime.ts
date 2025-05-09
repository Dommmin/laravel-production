import { type Message } from '@/types';
import { useEffect, useState } from 'react';

interface EchoMessageEvent {
    id: number;
    user_id: number;
    recipient_id: number;
    message: string;
    created_at: string;
}

interface UseChatRealtimeProps {
    initialMessages: Message[];
    currentUserId: number;
    selectedUserId?: number;
}

export const useChatRealtime = ({ initialMessages, currentUserId, selectedUserId }: UseChatRealtimeProps) => {
    const [messages, setMessages] = useState<Message[]>(initialMessages);

    // Update messages when initialMessages changes (e.g., from pagination)
    useEffect(() => {
        // When loading more messages via pagination, we want to prepend them
        // rather than replace the entire list
        if (initialMessages.length > 0 && messages.length > 0) {
            // Check if we're loading a new page of messages
            const oldestMessageId = Math.min(...messages.map((m) => m.id));
            const hasNewOlderMessages = initialMessages.some((m) => m.id < oldestMessageId);

            if (hasNewOlderMessages) {
                // Add only messages that aren't already in the list
                const existingIds = new Set(messages.map((m) => m.id));
                const newMessages = initialMessages.filter((m) => !existingIds.has(m.id));

                setMessages((prevMessages) => [...newMessages, ...prevMessages]);
                return;
            }
        }

        // If we get here, it's either the initial load or a complete refresh
        setMessages(initialMessages);
    }, [initialMessages, messages.length]);

    // Setup Echo listener for real-time messages
    useEffect(() => {
        if (!selectedUserId || !currentUserId || typeof window.Echo === 'undefined') {
            return;
        }

        const channel = window.Echo.private(`chat.${currentUserId}`);

        channel.listen('MessageSent', (event: EchoMessageEvent) => {
            // Only add messages relevant to the current conversation
            if (
                (event.user_id === selectedUserId && event.recipient_id === currentUserId) ||
                (event.user_id === currentUserId && event.recipient_id === selectedUserId)
            ) {
                setMessages((prevMessages) => {
                    // Check if the message already exists to prevent duplicates
                    const messageExists = prevMessages.some((msg) => msg.id === event.id);
                    if (messageExists) {
                        return prevMessages;
                    }

                    // Add new message to the end (newest messages are at the end)
                    return [...prevMessages, event];
                });
            }
        });

        return () => {
            channel.stopListening('MessageSent');
        };
    }, [currentUserId, selectedUserId]);

    return { messages };
};
