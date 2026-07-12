import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/css/app.css'],
            refresh: true,
        }),
        vue(),
        {
            name: 'copy-themes',
            closeBundle() {
                const source = 'resources/css/themes';
                const dest = 'public/css/themes';

                if (fs.existsSync(source)) {
                    if (fs.existsSync(dest)) {
                        fs.rmSync(dest, { recursive: true, force: true });
                    }
                    fs.cpSync(source, dest, { recursive: true });
                    console.log('✅ Themes copied to public/css/themes');
                }
            }
        }
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            'codemirror': path.resolve(__dirname, 'node_modules/codemirror'),
            '@codemirror': path.resolve(__dirname, 'node_modules/@codemirror'),
            'prettier': path.resolve(__dirname, 'node_modules/prettier'),
        },
    },
    build: {
        chunkSizeWarningLimit: 2000,
    },
});
