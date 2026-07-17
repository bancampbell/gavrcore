import { createApp, h, type Component } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import GlobalLightbox from './components/shared/GlobalLightbox.vue';
import '../css/app.css';

createInertiaApp({
    resolve: (name: string) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')) as any,
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
