<template>
    <div class="site-wrapper" :class="themeClass">
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

        <main class="site-main">
            <div class="container">
                <!-- СЮДА ВСТАВЛЯЕТСЯ КОНТЕНТ СТРАНИЦЫ -->
                <slot />
            </div>
        </main>

        <footer class="site-footer">
            <div class="container">
                <p>&copy; {{ new Date().getFullYear() }} {{ appSettings.site_name || 'GavrCore CMS' }}. Все права защищены.</p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MenuItem from '@/components/shared/MenuItem.vue';

const page = usePage();
const mainMenu = page.props.mainMenu || [];
const appSettings = page.props.appSettings || {};
const currentTheme = page.props.currentTheme || 'default';

const mobileMenuOpen = ref(false);
const themeClass = computed(() => `theme-${currentTheme}`);

const getLinkUrl = (item) => {
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};
</script>
