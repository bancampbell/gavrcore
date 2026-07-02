<template>
    <div class="site-wrapper">
        <header class="site-header">
            <div class="container">
                <div class="header-inner">
                    <!-- Логотип -->
                    <Link href="/" class="logo">
                        {{ appSettings.site_name || 'GavrCore CMS' }}
                    </Link>

                    <!-- Десктопное меню -->
                    <nav class="menu">
                        <MenuItem
                            v-for="item in mainMenu"
                            :key="item.id"
                            :item="item"
                        />
                    </nav>

                    <!-- Бургер -->
                    <button class="burger" @click="mobileMenuOpen = !mobileMenuOpen">
                        ☰
                    </button>
                </div>

                <!-- Мобильное меню -->
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

import {ref, onMounted, watch} from 'vue';
import {Link, usePage} from '@inertiajs/vue3';
import MenuItem from '@/components/shared/MenuItem.vue';

const page = usePage();
const mainMenu = page.props.mainMenu || [];
const appSettings = page.props.appSettings || {};
const currentTheme = page.props.currentTheme || 'default';

const mobileMenuOpen = ref(false);

const getLinkUrl = (item) => {
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};

// Применяем тему
const applyTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme);

    // Динамически подгружаем CSS темы
    const linkId = 'dynamic-theme';
    const oldLink = document.getElementById(linkId);
    if (oldLink) {
        oldLink.remove();
    }

    const link = document.createElement('link');
    link.id = linkId;
    link.rel = 'stylesheet';
    link.href = `/css/themes/${theme}/theme.css`;
    document.head.appendChild(link);
};

onMounted(() => {
    applyTheme(currentTheme);
});

// Следим за изменением темы через props
watch(() => currentTheme, (newTheme) => {
    if (newTheme) {
        applyTheme(newTheme);
    }
});
</script>
