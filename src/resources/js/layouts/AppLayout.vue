<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col">
        <header class="bg-white/80 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-gray-100">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <Link href="/" class="group">
                        <h1 class="text-2xl font-black tracking-tight">
                            <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent transition-all duration-300 group-hover:scale-105 inline-block">
                                {{ appSettings.site_name || 'GavrCore CMS' }}
                            </span>
                        </h1>
                    </Link>

                    <nav class="flex items-center space-x-1 flex-wrap">
                        <MenuItem
                            v-for="item in mainMenu"
                            :key="item.id"
                            :item="item"
                        />
                    </nav>

                    <div class="md:hidden">
                        <button
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="relative w-10 h-10 flex items-center justify-center rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition-all duration-200 focus:outline-none"
                        >
                            <div class="absolute transform transition-all duration-300" :class="{ 'opacity-0 rotate-90 scale-0': mobileMenuOpen, 'opacity-100 rotate-0 scale-100': !mobileMenuOpen }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </div>
                            <div class="absolute transform transition-all duration-300" :class="{ 'opacity-100 rotate-0 scale-100': mobileMenuOpen, 'opacity-0 -rotate-90 scale-0': !mobileMenuOpen }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>

                <div
                    class="md:hidden overflow-hidden transition-all duration-300 ease-in-out"
                    :class="{ 'max-h-0 opacity-0': !mobileMenuOpen, 'max-h-screen opacity-100 mt-4': mobileMenuOpen }"
                >
                    <div class="border-t border-gray-100 pt-4 space-y-1">
                        <div v-for="item in mainMenu" :key="item.id">
                            <Link
                                :href="getLinkUrl(item)"
                                :target="item.target"
                                class="flex items-center justify-between px-4 py-3 rounded-xl text-gray-700 font-medium transition-all duration-200 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 hover:text-indigo-600"
                                :class="{ 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600': isActive(item) }"
                                @click="mobileMenuOpen = false"
                            >
                                {{ item.title }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <slot />
            </div>
        </main>

        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 text-center">
                <p class="text-gray-500 text-sm">&copy; {{ new Date().getFullYear() }} {{ appSettings.site_name || 'GavrCore CMS' }}. Все права защищены.</p>
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

const isActive = (item) => {
    const currentUrl = page.url;
    const itemUrl = getLinkUrl(item);

    if (itemUrl === '/') {
        return currentUrl === '/';
    }
    return currentUrl !== '/' && currentUrl.startsWith(itemUrl);
};
</script>
