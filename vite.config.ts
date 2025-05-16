import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import { defineConfig, loadEnv } from 'vite';


export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd());

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.tsx'],
                ssr: 'resources/js/ssr.tsx',
                refresh: true,
            }),
            react(),
            tailwindcss(),
        ],
        define: {
            'import.meta.env.VITE_REVERB_APP_KEY': JSON.stringify(env.VITE_REVERB_APP_KEY || ''),
            'import.meta.env.VITE_REVERB_HOST': JSON.stringify(env.VITE_REVERB_HOST || ''),
            'import.meta.env.VITE_REVERB_PORT': JSON.stringify(env.VITE_REVERB_PORT || ''),
            'import.meta.env.VITE_REVERB_SCHEME': JSON.stringify(env.VITE_REVERB_SCHEME || ''),
        },
        esbuild: {
            jsx: 'automatic',
        },
        resolve: {
            alias: {
                'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
            },
        },
        server: {
            host: '0.0.0.0',
            port: 5173,
            hmr: {
                host: 'localhost',
            },
            watch: {
                usePolling: true,
            },
        },
    };
});
