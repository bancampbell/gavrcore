<template>
    <aside
        ref="asideRef"
        :class="[
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
                    :toggle="() => onToggle('materials')"
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
                    :toggle="() => onToggle('navigation')"
                >
                    <SidebarLink href="/admin/menu" icon="menu">Менеджер меню</SidebarLink>
                    <SidebarLink href="/admin/menu/items/all" icon="list">Все меню</SidebarLink>
                </SidebarSection>

                <!-- ===== БЛОК: СТРУКТУРА ===== -->
                <SidebarSection
                    section-key="structure"
                    title="Структура"
                    :is-open="isOpen('structure')"
                    :toggle="() => onToggle('structure')"
                >
                    <SidebarLink href="/admin/media" icon="image">Медиа-менеджер</SidebarLink>
                    <SidebarLink href="/admin/galleries" icon="image">Галереи</SidebarLink>
                    <SidebarLink href="/admin/forms" icon="module">Формы</SidebarLink>
                    <!-- Обратная связь с счетчиком -->
                    <SidebarLink href="/admin/submissions" icon="message">
                        Обратная связь
                        <span
                            v-if="unreadCount > 0"
                            class="ml-1 inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium leading-none text-white bg-red-500 rounded-full"
                        >
                            {{ unreadCount }}
                        </span>
                    </SidebarLink>
                    <SidebarLink href="#" icon="module">Менеджер модулей</SidebarLink>
                </SidebarSection>

                <!-- ===== БЛОК: ПОЛЬЗОВАТЕЛИ (для админов) ===== -->
                <template v-if="isAdmin">
                    <SidebarSection
                        section-key="users"
                        title="Пользователи"
                        :is-open="isOpen('users')"
                        :toggle="() => onToggle('users')"
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
                        :toggle="() => onToggle('settings')"
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
import { computed, watch, ref, onMounted, onUnmounted, nextTick } from 'vue';
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

// ===== ПОЛУЧАЕМ КОЛИЧЕСТВО НЕПРОЧИТАННЫХ =====
const unreadCount = computed(() => {
    return (page.props as any).unreadCount || 0;
});

// ===== АККОРДЕОН =====
// maxOpen постоянно 10: composable ничего не закрывает и НЕ ВОССТАНАВЛИВАЕТ
// разделы сам — вся логика ниже, на измерении реального переполнения.
const maxOpen = ref(10);
const isResizing = ref(false);
let resizeTimer: any = null;

const { isOpen, toggleSection, setOpenSections } = useSidebarSections(maxOpen);

const asideRef = ref<HTMLElement | null>(null);
const lastToggled = ref<string | null>(null);

// Состояние по умолчанию и порог «полного» размера окна
const DEFAULT_SECTIONS = ['materials', 'navigation', 'structure'];
const FULL_HEIGHT = 900;

// Порядок закрытия при нехватке места («Материалы» никогда не закрываются).
const CLOSE_ORDER = ['settings', 'users', 'structure', 'navigation'];

// Если меню не влезает в высоту — закрываем разделы по CLOSE_ORDER,
// пока не влезет. Ничего не открывает обратно.
const fitMenu = async () => {
    await nextTick();
    const aside = asideRef.value;
    if (!aside) return;

    let guard = 0;
    while (aside.scrollHeight > aside.clientHeight + 1 && guard < 10) {
        const candidate = CLOSE_ORDER.find(
            (key) => key !== lastToggled.value && isOpen(key)
        );
        if (!candidate) break;
        toggleSection(candidate);
        guard++;
        await nextTick();
    }
};

// Клик по заголовку раздела
const onToggle = (key: string) => {
    lastToggled.value = key;
    toggleSection(key);
    void fitMenu(); // если стало не хватать места — закроется другой раздел
};

// Высота окна на момент последнего завершённого ресайза
let lastHeight = window.innerHeight;

const handleResize = () => {
    isResizing.value = true;

    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        isResizing.value = false;
        const height = window.innerHeight;

        // Окно восстановлено до полного размера — возвращаем всё по умолчанию
        if (height >= FULL_HEIGHT && lastHeight < FULL_HEIGHT) {
            setOpenSections(DEFAULT_SECTIONS);
        }
        lastHeight = height;

        void fitMenu();
    }, 200);
};

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    clearTimeout(resizeTimer);
});

// При загрузке всегда состояние по умолчанию:
// открыты Материалы, Навигация, Структура; Пользователи и Настройки закрыты.
watch(currentUrl, () => {
    setOpenSections(DEFAULT_SECTIONS);
}, { immediate: true });

// ===== ОСТАЛЬНОЕ =====
const isAdmin = computed(() => {
    if (!props.user) return false;
    return props.user.email === 'admin@example.com';
});
</script>
