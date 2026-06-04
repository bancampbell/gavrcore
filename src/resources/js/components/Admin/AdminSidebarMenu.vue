<template>
    <aside :class="[
        'fixed lg:relative z-30 w-64 bg-white border-r border-gray-200 h-screen overflow-y-auto transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="py-4">
            <div class="px-3 mb-4">
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Менеджер меню</div>
                    <div class="text-sm text-gray-700">Управление навигацией</div>
                </div>
            </div>

            <nav class="space-y-2">
                <!-- Менеджер меню (список типов) -->
                <SidebarLink
                    href="/admin/menu"
                    icon="menu"
                    :active="isActive('/admin/menu')"
                >
                    Менеджер меню
                </SidebarLink>

                <!-- Все меню (пункты текущего типа) -->
                <SidebarLink
                    :href="allMenuLink"
                    icon="list"
                    :active="isActive(allMenuLink) && !isActive('/admin/menu')"
                    :disabled="!currentMenuTypeId"
                >
                    Все меню
                </SidebarLink>
            </nav>

            <!-- Дополнительная информация -->
            <div v-if="currentMenuTypeTitle && isMenuItemsPage" class="mt-6 px-3 pt-4 border-t border-gray-200">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Текущее меню</div>
                <div class="text-sm text-gray-700 bg-gray-50 rounded-lg p-2">
                    {{ currentMenuTypeTitle }}
                </div>
            </div>
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

// Текущий URL
const currentUrl = computed(() => window.location.pathname);

// Проверка активного пункта
const isActive = (url: string) => {
    if (url === '/admin/menu') {
        return currentUrl.value === '/admin/menu';
    }
    return currentUrl.value.startsWith(url);
};

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

// Ссылка на "Все меню" - ведёт на пункты текущего типа
const allMenuLink = computed(() => {
    if (currentMenuTypeId.value) {
        return `/admin/menu/types/${currentMenuTypeId.value}/items`;
    }
    return '#';
});
</script>
