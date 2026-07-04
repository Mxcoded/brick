import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    root: resolve(__dirname),

    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-website',
            input: [
                'resources/js/app.tsx',
                'resources/assets/sass/app.scss',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],

    build: {
        outDir: resolve(__dirname, '../../public/build-website'),
        emptyOutDir: true,
        manifest: true,
    },
});