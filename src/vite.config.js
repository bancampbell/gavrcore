import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inertia from '@inertiajs/vite';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/css/app.css'],
            refresh: true,
        }),
        vue(),
        inertia({
            pages: [
                'resources/js/Pages/**/*.vue',
                'resources/js/themes/**/*.vue',
            ],
        }),
        {
            name: 'copy-themes',
            closeBundle() {
                const themesSource = 'resources/css/themes';
                const themesDest = 'public/css/themes';

                if (fs.existsSync(themesSource)) {
                    if (fs.existsSync(themesDest)) {
                        fs.rmSync(themesDest, { recursive: true, force: true });
                    }
                    fs.cpSync(themesSource, themesDest, { recursive: true });
                    console.log('✅ Themes copied to public/css/themes');
                }

                const sharedSource = 'resources/css/shared';
                const sharedDest = 'public/css/shared';

                if (fs.existsSync(sharedSource)) {
                    if (fs.existsSync(sharedDest)) {
                        fs.rmSync(sharedDest, { recursive: true, force: true });
                    }
                    fs.cpSync(sharedSource, sharedDest, { recursive: true });
                    console.log('✅ Shared copied to public/css/shared');
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
