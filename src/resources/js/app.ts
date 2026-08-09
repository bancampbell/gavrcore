import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
    resolve: async (name) => {
        const pages = import.meta.glob<DefineComponent>([
            './Pages/**/*.vue',
            './modules/**/*.vue',
        ]);

        let page = pages[`./Pages/${name}.vue`];

        if (!page) {
            // MaterialManager/Index → ./modules/MaterialManager/ui/Index.vue
            const parts = name.split('/');
            if (parts.length >= 2) {
                const moduleName = parts[0];
                const pagePath = parts.slice(1).join('/');
                page = pages[`./modules/${moduleName}/ui/${pagePath}.vue`];
            }
        }

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return (await page()).default;
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
});
