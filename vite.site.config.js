import { defineConfig } from 'vite';
import path from 'node:path';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [tailwindcss()],
    publicDir: false,
    build: {
        outDir: 'public/site',
        emptyOutDir: false,
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/site/site.css'),
            output: {
                assetFileNames: (assetInfo) => {
                    if (assetInfo.names.includes('site.css') || assetInfo.originalFileNames.includes('resources/site/site.css')) {
                        return 'site.css';
                    }

                    return 'assets/[name]-[hash][extname]';
                },
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
            },
        },
    },
});
