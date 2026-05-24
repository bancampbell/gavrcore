<template>
    <aside :class="[
        'fixed lg:relative z-30 w-64 bg-white border-r border-gray-200 h-screen overflow-y-auto transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="py-4">
            <div class="px-3 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Версия</div>
                    <div class="text-sm text-gray-700">GavrCore CMS 1.0.0</div>
                </div>
            </div>

            <nav class="space-y-4">
                <div class="px-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">МАТЕРИАЛЫ</div>
                    <SidebarLink href="#" icon="plus">Создать материал</SidebarLink>
                    <SidebarLink href="/admin/materials" icon="list">Менеджер материалов</SidebarLink>
                    <SidebarLink href="#" icon="category">Категории</SidebarLink>
                    <SidebarLink href="#" icon="image">Медиа-менеджер</SidebarLink>
                </div>

                <div class="px-3">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-3">СТРУКТУРА</div>
                    <SidebarLink href="#" icon="menu">Менеджер меню</SidebarLink>
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
        </div>
    </aside>
</template>

<script setup>
import SidebarLink from './SidebarLink.vue';

defineProps({
    isOpen: Boolean
});
</script>
