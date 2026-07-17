<template>
    <div class="site-wrapper theme-warm">
        <Header :main-menu="mainMenu" :app-settings="appSettings" />
        <main class="site-main-warm">
            <div class="container">
                <slot />
            </div>
        </main>
        <Footer :app-settings="appSettings" />
    </div>
</template>

<script setup>
import Header from '../components/Header.vue';
import Footer from '../components/Footer.vue';

defineProps({
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
        default: 'warm'
    }
});
</script>
