<template>
    <aside :class="[
        'admin-sidebar fixed lg:relative z-30 w-64 bg-white border-r border-gray-200 h-full overflow-y-auto transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
        <div class="py-3">
            <template v-if="isMenuManagerPage">
                <div class="pl-3 mb-4">
                    <div class="bg-white/10 rounded-lg p-3 border border-white/20">
                        <div class="text-xs font-semibold text-white/70 uppercase tracking-wider">Менеджер меню</div>
                        <div class="text-sm text-white/90">Управление навигацией</div>
                    </div>
                </div>

                <nav class="space-y-2">
                    <SidebarLink href="/admin/menu" icon="menu" :active="currentUrl === '/admin/menu'">
                        Менеджер меню
                    </SidebarLink>
                    <SidebarLink href="/admin/menu/items/all" icon="list" :active="currentUrl === '/admin/menu/items/all'">
                        Все меню
                    </SidebarLink>
                </nav>

                <div v-if="currentMenuTypeTitle && isMenuItemsPage && currentMenuTypeId !== null" class="mt-6 pl-3 pt-4 border-t border-white/10">
                    <div class="text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">Текущее меню</div>
                    <div class="text-sm text-white/80 bg-white/10 rounded-lg p-2">
                        {{ currentMenuTypeTitle }}
                    </div>
                </div>
            </template>

            <template v-else-if="isUserManagerPage">
                <div class="pl-3 mb-4">
                    <div class="bg-white/10 rounded-lg p-3 border border-white/20">
                        <div class="text-xs font-semibold text-white/70 uppercase tracking-wider">Менеджер пользователей</div>
                        <div class="text-sm text-white/90">Управление пользователями и правами</div>
                    </div>
                </div>

                <nav class="space-y-2">
                    <SidebarLink
                        href="/admin/users"
                        icon="users"
                        :active="currentUrl === '/admin/users' || currentUrl.startsWith('/admin/users/')"
                    >
                        Пользователи
                    </SidebarLink>

                    <SidebarLink
                        href="/admin/groups"
                        icon="users"
                        :active="currentUrl.startsWith('/admin/groups')"
                    >
                        Группы пользователей
                    </SidebarLink>

                    <SidebarLink
                        href="/admin/access-levels"
                        icon="users"
                        :active="currentUrl === '/admin/access-levels'"
                    >
                        Уровни доступа
                    </SidebarLink>
                </nav>
            </template>

            <template v-else>
                <nav class="space-y-4">
                    <div class="pl-3">
                        <div class="section-title">Материалы</div>
                        <SidebarLink href="/admin/materials/create" icon="plus">Создать материал</SidebarLink>
                        <SidebarLink href="/admin/materials" icon="list">Менеджер материалов</SidebarLink>
                        <SidebarLink href="/admin/categories" icon="category">Категории</SidebarLink>
                        <SidebarLink href="/admin/media" icon="image">Медиа-менеджер</SidebarLink>
                    </div>

                    <div class="section-divider"></div>

                    <div class="pl-3">
                        <div class="section-title">Структура</div>
                        <SidebarLink href="/admin/menu" icon="menu">Менеджер меню</SidebarLink>
                        <SidebarLink href="/admin/galleries" icon="image">Галереи</SidebarLink>
                        <SidebarLink href="#" icon="module">Менеджер модулей</SidebarLink>
                    </div>

                    <div v-if="isAdmin" class="section-divider"></div>

                    <div v-if="isAdmin" class="pl-3">
                        <div class="section-title">Пользователи</div>
                        <SidebarLink
                            href="/admin/users"
                            icon="users"
                            :active="currentUrl === '/admin/users' || currentUrl.startsWith('/admin/users/')"
                        >
                            Пользователи
                        </SidebarLink>

                        <SidebarLink
                            href="/admin/groups"
                            icon="users"
                            :active="currentUrl.startsWith('/admin/groups')"
                        >
                            Группы пользователей
                        </SidebarLink>

                        <SidebarLink
                            href="/admin/access-levels"
                            icon="users"
                            :active="currentUrl === '/admin/access-levels'"
                        >
                            Уровни доступа
                        </SidebarLink>

                        <div class="pl-3 py-2 text-xs text-white/40 mt-1">Срочных запросов нет.</div>
                    </div>

                    <div v-if="isAdmin" class="section-divider"></div>

                    <div v-if="isAdmin" class="pl-3">
                        <div class="section-title">Настройки</div>
                        <SidebarLink href="/admin/settings" icon="settings">Общие настройки</SidebarLink>
                        <SidebarLink href="/admin/themes" icon="palette">Темы оформления</SidebarLink>
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
    user?: any;
}

const props = defineProps<Props>();

const page = usePage();
const currentUrl = computed(() => window.location.pathname);

const currentMenuTypeId = computed(() => {
    const pageProps = page.props as any;
    return pageProps.menuTypeId || null;
});

const currentMenuTypeTitle = computed(() => {
    const pageProps = page.props as any;
    return pageProps.menuTypeTitle || null;
});

const isMenuItemsPage = computed(() => {
    return currentUrl.value.includes('/admin/menu/types/') && currentUrl.value.includes('/items');
});

const isMenuManagerPage = computed(() => {
    return currentUrl.value.startsWith('/admin/menu');
});

const isUserManagerPage = computed(() => {
    return currentUrl.value.startsWith('/admin/users') ||
        currentUrl.value.startsWith('/admin/groups') ||
        currentUrl.value.startsWith('/admin/access-levels');
});

const isAdmin = computed(() => {
    if (!props.user) return false;
    return props.user.email === 'admin@example.com';
});
</script>
