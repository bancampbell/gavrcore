<template>
    <div class="site-wrapper" :class="`theme-${currentTheme}`">
        <Header :main-menu="mainMenu" :app-settings="appSettings" />
        <main class="site-main">
            <div class="container">
                <slot />
            </div>
        </main>
        <Footer :app-settings="appSettings" />
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import Header from '@/themes/default/components/Header.vue';
import Footer from '@/themes/default/components/Footer.vue';

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

console.log('DefaultLayout mainMenu:', mainMenu);
</script>
