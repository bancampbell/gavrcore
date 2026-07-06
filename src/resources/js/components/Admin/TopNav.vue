<template>
    <nav class="admin-topnav">
        <div class="admin-topnav-inner">
            <div class="admin-topnav-left">
                <button @click="$emit('toggleSidebar')" class="admin-topnav-toggle">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="admin-topnav-brand">
                    <Link href="/admin/dashboard" class="admin-topnav-logo" style="color: #ffffff !important;">
                        GavrCore CMS
                    </Link>
                </div>

                <div class="admin-topnav-menu">
                    <DropdownMenu title="Система" :items="systemItems" />
                    <DropdownMenu title="Пользователи" :items="userItems" />
                    <DropdownMenu title="Меню" :items="menuItems" />
                    <DropdownMenu title="Материалы" :items="materialsItems" />
                </div>
            </div>

            <div class="admin-topnav-right">
                <a
                    href="/"
                    target="_blank"
                    class="admin-topnav-view-site no-style"
                    title="Просмотр сайта"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span class="hidden sm:inline">Просмотр сайта</span>
                </a>
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
