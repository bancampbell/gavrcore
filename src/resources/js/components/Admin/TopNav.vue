<template>
    <nav class="fixed top-0 left-0 right-0 z-40 bg-gradient-to-r from-gray-800 to-gray-900 shadow-md">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-12">
                <div class="flex items-center">
                    <button @click="$emit('toggleSidebar')" class="lg:hidden mr-3 text-gray-300 hover:text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex-shrink-0">
                        <Link href="/admin/dashboard" class="text-lg font-semibold text-white hover:text-gray-300 transition">
                            GavrCore CMS
                        </Link>
                    </div>

                    <div class="hidden md:flex md:ml-4 space-x-1">
                        <DropdownMenu title="Система" :items="systemItems" />
                        <DropdownMenu title="Пользователи" :items="userItems" />
                        <DropdownMenu title="Меню" :items="menuItems" />
                        <DropdownMenu title="Материалы" :items="materialsItems" />
                    </div>
                </div>

                <UserMenu :user="user" @logout="$emit('logout')" />
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import DropdownMenu from './DropdownMenu.vue';
import UserMenu from './UserMenu.vue';
import type { User } from '../../types';

interface Props {
    user: User;
}

defineProps<Props>();

defineEmits<{
    (e: 'toggleSidebar'): void;
    (e: 'logout'): void;
}>();

interface MenuItem {
    name: string;
    href: string;
}

const systemItems: MenuItem[] = [
    { name: 'Панель управления', href: '/admin/dashboard' },
    { name: 'Общие настройки', href: '#' }
];

const userItems: MenuItem[] = [
    { name: 'Менеджер пользователей', href: '#' },
    { name: 'Создать пользователя', href: '#' }
];

const menuItems: MenuItem[] = [
    { name: 'Менеджер меню', href: '/admin/menu' },
    { name: 'Все меню', href: '/admin/menu/types/1/items' }
];

const materialsItems: MenuItem[] = [
    { name: 'Менеджер материалов', href: '/admin/materials' },
    { name: 'Категории', href: '/admin/categories' }
];
</script>
