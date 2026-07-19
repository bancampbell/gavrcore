import { createApp, h, type Component } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import GlobalLightbox from './components/shared/GlobalLightbox.vue';
import '../css/app.css';

createInertiaApp({
    resolve: (name: string) => {
        // Сначала ищем в Pages
        const pages = import.meta.glob('./Pages/**/*.vue');
        // Потом в themes
        const themes = import.meta.glob('./themes/**/*.vue');

        // Пробуем найти в Pages
        if (pages[`./Pages/${name}.vue`]) {
            return resolvePageComponent(`./Pages/${name}.vue`, pages);
        }

        // Пробуем найти в themes
        if (themes[`./themes/${name}.vue`]) {
            return resolvePageComponent(`./themes/${name}.vue`, themes);
        }

        throw new Error(`Page not found: ./Pages/${name}.vue or ./themes/${name}.vue`);
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({
            render: () => h(App as Component, props)
        });
        app.use(plugin);
        app.mount(el);
        return app;
    },
});

// Монтируем глобальный лайтбокс
const lightboxContainer = document.createElement('div');
document.body.appendChild(lightboxContainer);
const lightboxApp = createApp(GlobalLightbox);
lightboxApp.mount(lightboxContainer);

if (typeof window !== 'undefined') {
    (window as any).__lightboxApp = lightboxApp;
}
