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
                // Копируем темы в public/css/themes после сборки
                const source = 'resources/css/themes';
                const dest = 'public/css/themes';

                if (fs.existsSync(source)) {
                    // Удаляем старую папку
                    if (fs.existsSync(dest)) {
                        fs.rmSync(dest, { recursive: true, force: true });
                    }

                    // Копируем
                    fs.cpSync(source, dest, { recursive: true });
                    console.log('✅ Themes copied to public/css/themes');
                }
            }
        }
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        chunkSizeWarningLimit: 2000,
    },
});
