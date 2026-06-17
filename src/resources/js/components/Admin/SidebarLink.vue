<template>
    <Link
        :href="href"
        :class="[
            'flex items-center px-3 py-2 text-sm rounded-md transition-colors group mt-1',
            isActive
                ? 'bg-indigo-100 text-[#3071a9] font-semibold'
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
        ]"
    >
        <svg v-if="icon === 'plus'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <svg v-if="icon === 'list'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <svg v-if="icon === 'category'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"></path>
        </svg>
        <svg v-if="icon === 'image'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <svg v-if="icon === 'menu'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <svg v-if="icon === 'module'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
        <svg v-if="icon === 'users'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <svg v-if="icon === 'settings'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <svg v-if="icon === 'trash'" class="w-5 h-5 mr-3 transition-colors" :class="isActive ? 'text-[#3071a9]' : 'text-gray-400 group-hover:text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        <slot />
    </Link>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

interface Props {
    href: string;
    icon: 'plus' | 'list' | 'category' | 'image' | 'menu' | 'module' | 'users' | 'settings' | 'trash';
}

const props = defineProps<Props>();

const page = usePage();

const isActive = computed(() => {
    const currentUrl = page.url;

    // Точное совпадение
    if (currentUrl === props.href) {
        return true;
    }

    // Для менеджера материалов - подсветка на всех страницах материалов (кроме create/edit? или включая)
    if (props.href === '/admin/materials' && currentUrl.startsWith('/admin/materials')) {
        return true;
    }

    // Для категорий
    if (props.href === '/admin/categories' && currentUrl.startsWith('/admin/categories')) {
        return true;
    }

    // Для медиа
    if (props.href === '/admin/media' && currentUrl.startsWith('/admin/media')) {
        return true;
    }

    // Для менеджера меню - только точное совпадение
    if (props.href === '/admin/menu' && currentUrl === '/admin/menu') {
        return true;
    }

    // Для страниц "Все меню" (пункты конкретного типа)
    if (props.href.includes('/admin/menu/types/') && props.href.includes('/items')) {
        if (currentUrl.includes('/admin/menu/types/') && currentUrl.includes('/items')) {
            return true;
        }
    }

    // Для корзины
    if (props.href === '/admin/materials/trash' && currentUrl === '/admin/materials/trash') {
        return true;
    }

    // Для создания материала
    if (props.href === '/admin/materials/create' && currentUrl === '/admin/materials/create') {
        return true;
    }

    // Для редактирования материала
    if (props.href === '/admin/materials' && currentUrl.includes('/admin/materials/') && currentUrl.includes('/edit')) {
        return true;
    }

    // Для редактирования категории
    if (props.href === '/admin/categories' && currentUrl.includes('/admin/categories/') && currentUrl.includes('/edit')) {
        return true;
    }

    return false;
});
</script>
