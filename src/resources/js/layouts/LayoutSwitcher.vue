<template>
    <component :is="CurrentLayout" v-if="CurrentLayout">
        <slot />
    </component>
    <div v-else class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            <p class="mt-2 text-gray-500">Загрузка...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, shallowRef } from 'vue';
import {usePage} from '@inertiajs/vue3';

const page = usePage();

const layouts = {
    default: () => import('@/layouts/DefaultLayout.vue'),
    warm: () => import('@/layouts/WarmLayout.vue'),
    landing: () => import('@/layouts/LandingLayout.vue'),
    admin: () => import('@/layouts/AdminLayout.vue'),
    empty: () => import('@/layouts/EmptyLayout.vue'),
};

const CurrentLayout = shallowRef(null);

const loadThemeCSS = (theme) => {
    document.querySelectorAll('.dynamic-theme').forEach(el => el.remove());

    // Для лендинга свои CSS файлы
    const themeFiles = theme === 'landing'
        ? ['variables.css', 'theme.css', 'landing.css']
        : ['variables.css', 'theme.css', 'layout.css', 'header.css', 'footer.css', 'components.css', 'content.css', 'forms.css'];

    const baseUrl = `/css/themes/${theme}`;

    themeFiles.forEach((file) => {
        const link = document.createElement('link');
        link.className = 'dynamic-theme';
        link.rel = 'stylesheet';
        link.href = `${baseUrl}/${file}`;
        document.head.appendChild(link);
    });
};

const loadLayout = async (layoutName) => {
    const globalTheme = page.props.currentTheme || 'default';
    loadThemeCSS(globalTheme);

    try {
        const loader = layouts[layoutName] || layouts.default;
        const module = await loader();
        CurrentLayout.value = module.default;
    } catch {
        const module = await layouts.default();
        CurrentLayout.value = module.default;
    }
};

onMounted(() => {
    const theme = page.props.currentTheme || 'default';
    loadLayout(theme);
});

watch(() => page.props.currentTheme, (newTheme) => {
    if (newTheme) {
        loadLayout(newTheme);
    }
});
</script>
