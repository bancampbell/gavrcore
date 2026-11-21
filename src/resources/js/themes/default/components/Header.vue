<template>
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <Link href="/" class="logo">
                    <!-- Место под логотип: замените бейдж на свою картинку —
                         <img src="/logo.png" alt="Логотип" class="logo-img"> -->
                    <span class="logo-badge">⚡</span>

                    <span class="logo-text">
                        <span class="logo-name">{{ appSettings.site_name || 'GavrCore CMS' }}</span>
                        <span class="logo-desc">Сетевая компания</span>
                    </span>
                </Link>

                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

                <nav class="menu">
                    <MenuItem
                        v-for="item in mainMenu"
                        :key="item.id"
                        :item="item"
                    />
                </nav>

                <!-- ===== БЛОК АУТЕНТИФИКАЦИИ ===== -->
                <div class="auth-block">
                    <template v-if="user">
                        <span class="user-name">{{ user.name }}</span>
                        <button @click="logout" class="logout-btn">Выйти</button>
                    </template>
                    <template v-else>
                        <Link href="/login" class="login-link">Войти</Link>
                        <Link href="/register" class="register-link">Регистрация</Link>
                    </template>
                </div>

                <button class="burger" @click="mobileMenuOpen = !mobileMenuOpen">
                    <span></span>
                    <span></span>
                    <span></span>
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
                <!-- Мобильная аутентификация -->
                <div class="mobile-auth">
                    <template v-if="user">
                        <span class="mobile-user">{{ user.name }}</span>
                        <button @click="logout" class="mobile-logout">Выйти</button>
                    </template>
                    <template v-else>
                        <Link href="/login" class="mobile-login">Войти</Link>
                        <Link href="/register" class="mobile-register">Регистрация</Link>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import {ref, computed} from 'vue';
import {Link, usePage, router} from '@inertiajs/vue3';
import MenuItem from '@/components/shared/MenuItem.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || null);

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
    return item.url || '#';
};

const logout = () => {
    router.post('/logout');
};
</script>

<!-- Все стили перенесены в resources/css/themes/default/header.css -->
