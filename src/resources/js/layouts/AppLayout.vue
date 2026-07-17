<template>
    <component
        :is="CurrentLayout"
        v-if="CurrentLayout"
        :main-menu="mainMenu"
        :app-settings="appSettings"
        :current-theme="currentTheme"
    >
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
import {ref, watch, onMounted} from 'vue';

const props = defineProps({
    mainMenu: {
        type: Array,
        default: () => []
    },
    appSettings: {
        type: Object,
        default: () => ({})
    },
    currentTheme: {
        type: String,
        default: 'default'
    }
});

const CurrentLayout = ref(null);

const layouts = {
    default: () => import('@/themes/default/layouts/AppLayout.vue'),
    warm: () => import('@/themes/warm/layouts/AppLayout.vue'),
};

const loadLayout = async (theme) => {
    document.querySelectorAll('.dynamic-theme').forEach(el => el.remove());

    const themeFiles = [
        'variables.css',
        'theme.css',
        'layout.css',
        'header.css',
        'footer.css',
        'components.css',
        'content.css',
        'forms.css'
    ];

    const baseUrl = `/css/themes/${theme}`;

    const loadPromises = themeFiles.map((file) => {
        return new Promise((resolve) => {
            const link = document.createElement('link');
            link.className = 'dynamic-theme';
            link.rel = 'stylesheet';
            link.href = `${baseUrl}/${file}`;
            link.onload = resolve;
            link.onerror = resolve;
            document.head.appendChild(link);
        });
    });

    await Promise.all(loadPromises);

    const loadLayoutFn = layouts[theme] || layouts.default;
    const module = await loadLayoutFn();
    CurrentLayout.value = module.default;
};

onMounted(() => {
    loadLayout(props.currentTheme);
});

watch(() => props.currentTheme, (newTheme) => {
    if (newTheme) {
        loadLayout(newTheme);
    }
});
</script>
