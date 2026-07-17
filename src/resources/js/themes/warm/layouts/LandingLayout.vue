<template>
    <div class="landing-wrapper theme-warm">
        <!-- Хедер лендинга (упрощенный) -->
        <header class="landing-header">
            <div class="container">
                <div class="landing-header-inner">
                    <Link href="/" class="landing-logo">
                        <span class="landing-logo-icon">✦</span>
                        <span class="landing-logo-text">{{ appSettings.site_name || 'GavrCore CMS' }}</span>
                    </Link>

                    <nav class="landing-nav">
                        <a href="#features">Преимущества</a>
                        <a href="#pricing">Цены</a>
                        <a href="#contact">Контакты</a>
                        <Link href="/admin" class="landing-btn-primary">Войти</Link>
                    </nav>

                    <button class="landing-burger" @click="mobileMenuOpen = !mobileMenuOpen">
                        ☰
                    </button>
                </div>

                <!-- Мобильное меню -->
                <div class="landing-mobile-menu" :class="{ open: mobileMenuOpen }">
                    <a href="#features" @click="mobileMenuOpen = false">Преимущества</a>
                    <a href="#pricing" @click="mobileMenuOpen = false">Цены</a>
                    <a href="#contact" @click="mobileMenuOpen = false">Контакты</a>
                    <Link href="/admin" @click="mobileMenuOpen = false">Войти</Link>
                </div>
            </div>
        </header>

        <!-- Контент страницы (секции) -->
        <slot />

        <!-- Футер лендинга -->
        <footer class="landing-footer">
            <div class="container">
                <div class="landing-footer-inner">
                    <div>
                        <h4>{{ appSettings.site_name || 'GavrCore CMS' }}</h4>
                        <p>{{ appSettings.site_description || 'Современная CMS на Laravel и Vue.js' }}</p>
                    </div>
                    <div>
                        <p>&copy; {{ new Date().getFullYear() }} Все права защищены.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    appSettings: {
        type: Object,
        default: () => ({})
    }
});

const mobileMenuOpen = ref(false);
</script>

<style>
@import '../../../css/themes/warm/landing.css';
</style>
