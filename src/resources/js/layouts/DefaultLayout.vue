<template>
    <div class="site-wrapper" :class="`theme-${currentTheme}`">
        <component
            v-if="HeaderComponent"
            :is="HeaderComponent"
            :main-menu="mainMenu"
            :app-settings="appSettings"
        />
        <main class="site-main">
            <div class="container">
                <slot />
            </div>
        </main>
        <component
            v-if="FooterComponent"
            :is="FooterComponent"
            :app-settings="appSettings"
        />
        <CookieConsent />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CookieConsent from '@/components/shared/CookieConsent.vue';

const page = usePage();

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

const mainMenu = props.mainMenu.length ? props.mainMenu : (page.props.mainMenu || []);
const appSettings = Object.keys(props.appSettings).length ? props.appSettings : (page.props.appSettings || {});
const currentTheme = props.currentTheme || page.props.currentTheme || 'default';

const HeaderComponent = ref(null);
const FooterComponent = ref(null);

const loadThemeComponents = async (theme) => {
    try {
        const header = await import(`@/themes/${theme}/components/Header.vue`);
        HeaderComponent.value = header.default;
    } catch {
        const header = await import(`@/themes/default/components/Header.vue`);
        HeaderComponent.value = header.default;
    }

    try {
        const footer = await import(`@/themes/${theme}/components/Footer.vue`);
        FooterComponent.value = footer.default;
    } catch {
        const footer = await import(`@/themes/default/components/Footer.vue`);
        FooterComponent.value = footer.default;
    }
};

onMounted(() => {
    loadThemeComponents(currentTheme);
});

watch(() => currentTheme, (newTheme) => {
    loadThemeComponents(newTheme);
});
</script>
