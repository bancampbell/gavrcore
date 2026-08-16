import { defineConfig } from 'vitest/config';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue() as any],
    test: {
        environment: 'jsdom',
        include: ['resources/js/__tests__/**/*.test.ts'],
        globals: true,
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
