<template>
    <header class="site-header-warm">
        <div class="container">
            <div class="header-inner-warm">
                <Link href="/" class="logo-warm">
                    <span class="logo-icon-warm">✦</span>
                    <span class="logo-text-warm">{{ appSettings.site_name || 'GavrCore CMS' }}</span>
                </Link>

                <nav class="menu-warm">
                    <MenuItem
                        v-for="item in mainMenu"
                        :key="item.id"
                        :item="item"
                    />
                </nav>

                <button class="burger-warm" @click="mobileMenuOpen = !mobileMenuOpen">
                    ☰
                </button>
            </div>

            <div class="mobile-menu-warm" :class="{ open: mobileMenuOpen }">
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
