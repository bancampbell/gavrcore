<template>
    <div class="site-wrapper theme-warm">
        <header class="site-header-warm">
            <div class="container">
                <div class="header-inner-warm">
                    <nav class="menu-warm">
                        <MenuItem
                            v-for="item in mainMenu"
                            :key="item.id"
                            :item="item"
                        />
                    </nav>
                    <Link href="/" class="logo-warm">
                        <span class="logo-icon-warm">✦</span>
                        <span class="logo-text-warm">{{ appSettings.site_name || 'GavrCore CMS' }}</span>
                    </Link>
                    <button class="burger-warm" @click="mobileMenuOpen = !mobileMenuOpen">
                        ☰
                    </button>
                </div>
                <div class="mobile-menu-warm" :class="{ open: mobileMenuOpen }">
                    <MenuItem
                        v-for="item in mainMenu"
                        :key="item.id"
                        :item="item"
                    />
                </div>
            </div>
        </header>

        <section class="hero-warm">
            <div class="container">
                <div class="hero-content-warm">
                    <h1 class="hero-title-warm">{{ appSettings.site_name || 'GavrCore CMS' }}</h1>
                    <p class="hero-desc-warm">{{ appSettings.site_description || 'Современная CMS на Laravel и Vue.js' }}</p>
                    <Link href="/admin" class="hero-btn-warm">Начать</Link>
                </div>
            </div>
        </section>

        <main class="site-main-warm">
            <div class="container">
                <slot />
            </div>
        </main>

        <footer class="site-footer-warm">
            <div class="container">
                <div class="footer-grid-warm">
                    <div class="footer-col-warm">
                        <h4>{{ appSettings.site_name || 'GavrCore CMS' }}</h4>
                        <p>{{ appSettings.site_description || '' }}</p>
                    </div>
                    <div class="footer-col-warm">
                        <h4>Навигация</h4>
                        <ul>
                            <li v-for="item in mainMenu" :key="item.id">
                                <Link :href="getLinkUrl(item)">{{ item.title }}</Link>
                            </li>
                        </ul>
                    </div>
                    <div class="footer-col-warm">
                        <h4>Контакты</h4>
                        <p>{{ appSettings.contact_email || 'info@example.com' }}</p>
                    </div>
                </div>
                <div class="footer-bottom-warm">
                    <p>&copy; {{ new Date().getFullYear() }} {{ appSettings.site_name || 'GavrCore CMS' }}. Все права защищены.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MenuItem from '@/components/shared/MenuItem.vue';

const page = usePage();
const mainMenu = page.props.mainMenu || [];
const appSettings = page.props.appSettings || {};

const mobileMenuOpen = ref(false);

const getLinkUrl = (item) => {
    if (item.link_type === 'url') return item.link_value || '/';
    if (item.link_type === 'material') return `/${item.link_value}`;
    if (item.link_type === 'category') return `/category/${item.link_value}`;
    return '#';
};
</script>
