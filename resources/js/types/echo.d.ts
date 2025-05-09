interface EchoChannel {
    listen: (event: string, callback: (e: any) => void) => EchoChannel;
    stopListening: (event?: string) => EchoChannel;
}

interface EchoPrivateChannel extends EchoChannel {
    whisper: (event: string, data: any) => EchoPrivateChannel;
}

interface Echo {
    channel: (channel: string) => EchoChannel;
    private: (channel: string) => EchoPrivateChannel;
    join: (channel: string) => EchoChannel;
    leave: (channel: string) => void;
    connect: () => void;
    disconnect: () => void;
}

declare global {
    interface Window {
        Echo: Echo;
    }
}

export {};
