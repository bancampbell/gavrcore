<template>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <Link href="/" class="logo">
                    {{ appSettings.site_name || 'GavrCore CMS' }}
                </Link>

                <nav class="menu">
                    <MenuItem
                        v-for="item in mainMenu"
                        :key="item.id"
                        :item="item"
                    />
                </nav>

                <button class="burger" @click="mobileMenuOpen = !mobileMenuOpen">
                    ☰
                </button>
            </div>

            <div class="mobile-menu" :class="{ open: mobileMenuOpen }">
                <div v-for="item in mainMenu" :key="item.id">
                    <Link
                        :href="getLinkUrl(item)"
                        :target="item.target"
                        @click="mobileMenuOpen = false"
                    >
                        {{ item.title }}
                    </Link>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import MenuItem from '@/components/shared/MenuItem.vue';

defineProps({
    mainMenu: {
        type: Array,
        default: () => []
    },
    appSettings: {
        type: Object,
        default: () => ({})
    }
});

const mobileMenuOpen = ref(false);

const getLinkUrl = (item) => {
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};
</script>
