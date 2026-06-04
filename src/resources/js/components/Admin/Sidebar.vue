<template>
    <aside :class="[
        'fixed lg:relative z-30 w-64 bg-white border-r border-gray-200 h-screen overflow-y-auto transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="py-4">
            <!-- Для страниц менеджера меню -->
            <template v-if="isMenuManagerPage">
                <div class="px-3 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Менеджер меню</div>
                        <div class="text-sm text-gray-700">Управление навигацией</div>
                    </div>
                </div>

                <nav class="space-y-2">
                    <SidebarLink
                        href="/admin/menu"
                        icon="menu"
                        :active="currentUrl === '/admin/menu'"
                    >
                        Менеджер меню
                    </SidebarLink>

                    <SidebarLink
                        href="/admin/menu/types/1/items"
                        icon="list"
                        :active="currentUrl.includes('/admin/menu/types/') && currentUrl.includes('/items')"
                    >
                        Все меню
                    </SidebarLink>
                </nav>

                <!-- Показываем только когда выбран конкретный тип меню (не "все пункты") -->
                <div v-if="currentMenuTypeTitle && isMenuItemsPage && currentMenuTypeId !== null" class="mt-6 px-3 pt-4 border-t border-gray-200">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Текущее меню</div>
                    <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-2">
                        {{ currentMenuTypeTitle }}
                    </div>
                </div>
            </template>

            <!-- Для всех остальных страниц (обычное меню) -->
            <template v-else>
                <div class="px-3 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Версия</div>
                        <div class="text-sm text-gray-700">GavrCore CMS 1.0.0</div>
                    </div>
                </div>

                <nav class="space-y-4">
                    <div class="px-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">МАТЕРИАЛЫ</div>
                        <SidebarLink href="/admin/materials/create" icon="plus">Создать материал</SidebarLink>
                        <SidebarLink href="/admin/materials" icon="list">Менеджер материалов</SidebarLink>
                        <SidebarLink href="/admin/categories" icon="category">Категории</SidebarLink>
                        <SidebarLink href="/admin/media" icon="image">Медиа-менеджер</SidebarLink>
                    </div>

                    <div class="px-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">СТРУКТУРА</div>
                        <SidebarLink href="/admin/menu" icon="menu">Менеджер меню</SidebarLink>
                        <SidebarLink href="#" icon="module">Менеджер модулей</SidebarLink>
                    </div>

                    <div class="px-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">ПОЛЬЗОВАТЕЛИ</div>
                        <SidebarLink href="#" icon="users">Менеджер пользователей</SidebarLink>
                        <div class="px-3 py-2 text-xs text-gray-400 mt-1">Срочных запросов нет.</div>
                    </div>

                    <div class="px-3">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">НАСТРОЙКИ</div>
                        <SidebarLink href="#" icon="settings">Общие настройки</SidebarLink>
                        <SidebarLink href="/admin/materials/trash" icon="trash">Корзина</SidebarLink>
                    </div>
                </nav>
            </template>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarLink from './SidebarLink.vue';

interface Props {
    isOpen: boolean;
}

defineProps<Props>();

const page = usePage();
const currentUrl = computed(() => window.location.pathname);

// ID текущего типа меню (из пропсов страницы MenuItems)
const currentMenuTypeId = computed(() => {
    const props = page.props as any;
    return props.menuTypeId || null;
});

// Название текущего типа меню
const currentMenuTypeTitle = computed(() => {
    const props = page.props as any;
    return props.menuTypeTitle || null;
});

// Является ли текущая страница страницей пунктов меню
const isMenuItemsPage = computed(() => {
    return currentUrl.value.includes('/admin/menu/types/') && currentUrl.value.includes('/items');
});

// Проверяем, находимся ли мы на страницах менеджера меню
const isMenuManagerPage = computed(() => {
    return currentUrl.value.startsWith('/admin/menu');
});
</script>
