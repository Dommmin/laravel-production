declare global {
    function route(name: 'chat.index'): string;
    function route(name: 'chat.send'): string;
    function route(name: string, params?: Record<string, any>): string;
}

export {};
