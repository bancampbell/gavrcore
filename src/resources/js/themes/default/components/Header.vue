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
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
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
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};

const logout = () => {
    router.post('/logout');
};
</script>

<style scoped>
/* ===== ХЕДЕР ===== */
.site-header {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    padding: 12px 0;
}

.header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.logo {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
    text-decoration: none;
    flex-shrink: 0;
}

.menu {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ===== АУТЕНТИФИКАЦИЯ ===== */
.auth-block {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-left: 20px;
    flex-shrink: 0;
}

.user-name {
    font-size: 14px;
    font-weight: 500;
    color: #1a1a2e;
}

.login-link {
    font-size: 14px;
    color: #4a4a6a;
    text-decoration: none;
    transition: color 0.2s;
}

.login-link:hover {
    color: #1a1a2e;
}

.register-link {
    font-size: 14px;
    background: #1a1a2e;
    color: white;
    padding: 6px 16px;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.2s;
}

.register-link:hover {
    background: #2a2a4e;
}

.logout-btn {
    font-size: 14px;
    color: #ef4444;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s;
}

.logout-btn:hover {
    background: #fef2f2;
}

/* ===== БУРГЕР ===== */
.burger {
    display: none;
    flex-direction: column;
    gap: 4px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
}

.burger span {
    display: block;
    width: 24px;
    height: 2px;
    background: #1a1a2e;
    border-radius: 2px;
}

/* ===== МОБИЛЬНОЕ МЕНЮ ===== */
.mobile-menu {
    display: none;
    flex-direction: column;
    gap: 8px;
    padding: 16px 0 8px;
    border-top: 1px solid #f0f0f0;
    margin-top: 12px;
}

.mobile-menu.open {
    display: flex;
}

.mobile-menu a {
    padding: 8px 0;
    color: #4a4a6a;
    text-decoration: none;
    font-size: 15px;
    font-weight: 500;
}

/* ===== МОБИЛЬНАЯ АУТЕНТИФИКАЦИЯ ===== */
.mobile-auth {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.mobile-user {
    font-size: 15px;
    font-weight: 500;
    color: #1a1a2e;
}

.mobile-login,
.mobile-register {
    font-size: 15px;
    text-decoration: none;
    padding: 8px 0;
}

.mobile-login {
    color: #4a4a6a;
}

.mobile-login:hover {
    color: #1a1a2e;
}

.mobile-register {
    color: #1a1a2e;
    font-weight: 500;
}

.mobile-logout {
    font-size: 15px;
    color: #ef4444;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px 0;
    text-align: left;
}

.mobile-logout:hover {
    color: #dc2626;
}

/* ===== АДАПТИВ ===== */
@media (max-width: 768px) {
    .menu {
        display: none;
    }

    .auth-block {
        display: none;
    }

    .burger {
        display: flex;
    }

    .mobile-auth {
        display: flex;
    }
}

@media (min-width: 769px) {
    .mobile-menu {
        display: none !important;
    }

    .mobile-auth {
        display: none !important;
    }
}
</style>
