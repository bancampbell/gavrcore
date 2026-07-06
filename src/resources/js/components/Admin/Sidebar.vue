<template>
    <aside :class="[
        'admin-sidebar fixed lg:relative z-30 w-64 bg-white border-r border-gray-200 h-full transition-transform duration-300 ease-in-out',
        isOpen ? 'translate-x-0' : '-translate-x-full',
        'lg:translate-x-0',
        isResizing ? 'overflow-hidden' : 'overflow-y-auto'
    ]">
        <div class="py-3">
            <nav class="space-y-1">
                <!-- ===== БЛОК: МАТЕРИАЛЫ ===== -->
                <SidebarSection
                    section-key="materials"
                    title="Материалы"
                    :is-open="isOpen('materials')"
                    :toggle="() => toggleSection('materials')"
                >
                    <SidebarLink href="/admin/materials" icon="list">Менеджер материалов</SidebarLink>
                    <SidebarLink href="/admin/categories" icon="category">Категории</SidebarLink>
                    <SidebarLink href="/admin/materials/trash" icon="trash">Корзина</SidebarLink>
                </SidebarSection>

                <!-- ===== БЛОК: НАВИГАЦИЯ ===== -->
                <SidebarSection
                    section-key="navigation"
                    title="Навигация"
                    :is-open="isOpen('navigation')"
                    :toggle="() => toggleSection('navigation')"
                >
                    <SidebarLink href="/admin/menu" icon="menu">Менеджер меню</SidebarLink>
                    <SidebarLink href="/admin/menu/items/all" icon="list">Все меню</SidebarLink>
                </SidebarSection>

                <!-- ===== БЛОК: СТРУКТУРА ===== -->
                <SidebarSection
                    section-key="structure"
                    title="Структура"
                    :is-open="isOpen('structure')"
                    :toggle="() => toggleSection('structure')"
                >
                    <SidebarLink href="/admin/media" icon="image">Медиа-менеджер</SidebarLink>
                    <SidebarLink href="/admin/galleries" icon="image">Галереи</SidebarLink>
                    <SidebarLink href="#" icon="module">Менеджер модулей</SidebarLink>
                </SidebarSection>

                <!-- ===== БЛОК: ПОЛЬЗОВАТЕЛИ (для админов) ===== -->
                <template v-if="isAdmin">
                    <SidebarSection
                        section-key="users"
                        title="Пользователи"
                        :is-open="isOpen('users')"
                        :toggle="() => toggleSection('users')"
                    >
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
                    </SidebarSection>
                </template>

                <!-- ===== БЛОК: НАСТРОЙКИ (для админов) ===== -->
                <template v-if="isAdmin">
                    <SidebarSection
                        section-key="settings"
                        title="Настройки"
                        :is-open="isOpen('settings')"
                        :toggle="() => toggleSection('settings')"
                    >
                        <SidebarLink href="/admin/settings" icon="settings">Общие настройки</SidebarLink>
                        <SidebarLink href="/admin/themes" icon="palette">Темы оформления</SidebarLink>
                    </SidebarSection>
                </template>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { computed, watch, ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarLink from './SidebarLink.vue';
import SidebarSection from './SidebarSection.vue';
import { useSidebarSections } from '../../composables/useSidebarSections';

interface Props {
    isOpen: boolean;
    user?: any;
}

const props = defineProps<Props>();

const page = usePage();
const currentUrl = computed(() => window.location.pathname);

// ===== АДАПТИВНОЕ КОЛИЧЕСТВО ОТКРЫТЫХ РАЗДЕЛОВ (ПО ВЫСОТЕ) =====
const getMaxOpen = () => {
    const height = window.innerHeight;
    if (height >= 900) return 10;
    if (height >= 700) return 4;
    if (height >= 550) return 3;
    return 2;
};

const maxOpen = ref(getMaxOpen());
const isResizing = ref(false);
let resizeTimer: any = null;

// ===== АККОРДЕОН =====
const { isOpen, toggleSection, setOpenSections } = useSidebarSections(maxOpen);

const handleResize = () => {
    isResizing.value = true;
    const newMax = getMaxOpen();
    if (newMax !== maxOpen.value) {
        maxOpen.value = newMax;
    }

    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        isResizing.value = false;
    }, 300);
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    clearTimeout(resizeTimer);
});

// Автоматически открываем нужный раздел при переходе
watch(currentUrl, () => {
    let targetSection = 'materials';

    if (currentUrl.value.startsWith('/admin/materials') || currentUrl.value.startsWith('/admin/categories') || currentUrl.value.startsWith('/admin/materials/trash')) {
        targetSection = 'materials';
    } else if (currentUrl.value.startsWith('/admin/menu')) {
        targetSection = 'navigation';
    } else if (currentUrl.value.startsWith('/admin/media') || currentUrl.value.startsWith('/admin/galleries')) {
        targetSection = 'structure';
    } else if (currentUrl.value.startsWith('/admin/users') || currentUrl.value.startsWith('/admin/groups') || currentUrl.value.startsWith('/admin/access-levels')) {
        targetSection = 'users';
    } else if (currentUrl.value.startsWith('/admin/settings') || currentUrl.value.startsWith('/admin/themes')) {
        targetSection = 'settings';
    }

    const allSections = ['materials', 'navigation', 'structure', 'users', 'settings'];
    setOpenSections(allSections);
}, { immediate: true });

// ===== ОСТАЛЬНОЕ =====
const isAdmin = computed(() => {
    if (!props.user) return false;
    return props.user.email === 'admin@example.com';
});
</script>
