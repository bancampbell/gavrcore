<template>
    <nav v-if="items && items.length > 0" class="flex items-center gap-2 text-sm text-gray-500 py-3 overflow-x-auto whitespace-nowrap" aria-label="Хлебные крошки">
        <template v-for="(item, index) in items" :key="index">
            <!-- Ссылка -->
            <Link
                v-if="item.url"
                :href="item.url"
                class="hover:text-indigo-600 transition-colors flex items-center gap-1.5"
            >
                <svg v-if="index === 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
                </svg>
                <span v-else>{{ item.title }}</span>
            </Link>

            <!-- Текущая страница (без ссылки) -->
            <span v-else class="text-gray-700 font-medium flex items-center gap-1.5">
                <svg v-if="index === 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z" />
                </svg>
                {{ item.title }}
            </span>

            <!-- Разделитель -->
            <span v-if="index < items.length - 1" class="text-gray-300 select-none">/</span>
        </template>
    </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface BreadcrumbItem {
    title: string;
    url: string | null;
}

defineProps<{
    items: BreadcrumbItem[];
}>();
</script>
