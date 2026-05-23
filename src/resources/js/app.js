import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import Index from './Pages/Index.vue';

createInertiaApp({
    resolve: () => Index,
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
