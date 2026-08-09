<template>
    <div class="flex-1 bg-white flex flex-col overflow-hidden">
        <div class="px-4 py-1.5 border-b border-gray-200 bg-gray-50">
            <div v-if="!showSearch" class="flex items-center gap-2">
                <button @click="onGoHome" class="p-1 rounded hover:bg-gray-100 transition" title="В корень">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </button>
                <button @click="onGoBack" :disabled="!canGoBack" class="p-1 rounded hover:bg-gray-100 transition disabled:opacity-30 disabled:cursor-not-allowed" title="Назад">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button @click="onSortAsc" class="p-1 rounded hover:bg-gray-100 transition" :class="{ 'text-blue-500': isAscActive }" title="Сортировка А-Я">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                    </svg>
                </button>
                <button @click="onSortDesc" class="p-1 rounded hover:bg-gray-100 transition" :class="{ 'text-blue-500': isDescActive }" title="Сортировка Я-А">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4 4m0 0l4-4m-4 4V4" />
                    </svg>
                </button>
                <div class="flex-1"></div>
                <button @click="onOpenSearch" class="p-1 rounded hover:bg-gray-100 transition" title="Поиск">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            <div v-else class="flex items-center gap-2 bg-blue-50 -my-1.5 -mx-4 px-4 py-1.5">
                <input :value="searchQuery" @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)" type="text" placeholder="Поиск..." class="flex-1 text-sm bg-transparent focus:outline-none" autofocus />
                <button @click="onClearSearch" class="p-1 rounded hover:bg-gray-100 transition flex-shrink-0" title="Закрыть">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-2 relative" ref="scrollContainer">
            <!-- Спиннер ТОЛЬКО при самой первой загрузке, когда ещё нет paginatedData -->
            <div v-if="loading && !paginatedData" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>

            <!-- Список НИКОГДА не прячем — только приглушаем при перезагрузке -->
            <div
                class="space-y-0.5"
                :class="{ 'opacity-50 pointer-events-none transition-opacity duration-150': loading && paginatedData }"
            >
                <FileItem
                    v-for="item in folders"
                    :key="item.path"
                    :item="item"
                    :is-selected="isSelected(item.path)"
                    :is-folder="true"
                    @select="onToggleSelect"
                    @click="onSelectFolder"
                    @open="onOpenFolder"
                />
                <FileItem
                    v-for="item in files"
                    :key="item.path"
                    :item="item"
                    :is-selected="isSelected(item.path)"
                    :is-folder="false"
                    :data-file-path="item.path"
                    @select="onToggleSelect"
                    @click="onSelectFile"
                />
            </div>

            <!-- Пустая папка -->
            <div v-if="!loading && folders.length === 0 && files.length === 0 && !showSearch" class="text-center py-12 text-gray-400">
                Папка пуста
            </div>
            <div v-if="showSearch && folders.length === 0 && files.length === 0 && !loading" class="text-center py-12 text-gray-400">
                Ничего не найдено
            </div>
        </div>

        <!-- Пагинация -->
        <div v-if="paginatedData && paginatedData.last_page > 1" class="border-t border-gray-200 px-4 py-2 bg-gray-50 flex items-center justify-between">
            <span class="text-sm text-gray-600">
                {{ (paginatedData.page - 1) * paginatedData.per_page + 1 }} -
                {{ Math.min(paginatedData.page * paginatedData.per_page, paginatedData.total) }}
                из {{ paginatedData.total }}
            </span>
            <div class="flex gap-1">
                <button
                    @click="$emit('page-change', paginatedData.page - 1)"
                    :disabled="paginatedData.page <= 1"
                    class="px-3 py-1 text-sm rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    ←
                </button>
                <template v-for="p in visiblePages" :key="String(p)">
                    <span
                        v-if="p === '...'"
                        class="px-3 py-1 text-sm text-gray-400 select-none"
                    >...</span>
                    <button
                        v-else
                        @click="$emit('page-change', p)"
                        class="px-3 py-1 text-sm rounded hover:bg-gray-200"
                        :class="{ 'bg-blue-600 text-white hover:bg-blue-700': p === paginatedData.page }"
                    >
                        {{ p }}
                    </button>
                </template>
                <button
                    @click="$emit('page-change', paginatedData.page + 1)"
                    :disabled="paginatedData.page >= paginatedData.last_page"
                    class="px-3 py-1 text-sm rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    →
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import FileItem from './FileItem.vue';
import type { MediaItem } from '../types';

const props = defineProps<{
    folders: MediaItem[];
    files: MediaItem[];
    loading: boolean;
    showSearch: boolean;
    searchQuery: string;
    isSelected: (path: string) => boolean;
    canGoBack: boolean;
    isAscActive: boolean;
    isDescActive: boolean;
    mode?: 'full' | 'picker';
    paginatedData?: {
        data: MediaItem[];
        total: number;
        page: number;
        per_page: number;
        last_page: number;
    } | null;
}>();

const emit = defineEmits<{
    (e: 'go-home'): void;
    (e: 'go-back'): void;
    (e: 'sort-asc'): void;
    (e: 'sort-desc'): void;
    (e: 'open-search'): void;
    (e: 'clear-search'): void;
    (e: 'update:searchQuery', value: string): void;
    (e: 'toggle-select', path: string, item: MediaItem): void;
    (e: 'select-folder', item: MediaItem): void;
    (e: 'open-folder', item: MediaItem): void;
    (e: 'select-file', item: MediaItem): void;
    (e: 'page-change', page: number): void;
}>();

const scrollContainer = ref<HTMLDivElement | null>(null);

watch(() => props.folders, () => {
    scrollContainer.value?.scrollTo({ top: 0, behavior: 'smooth' });
}, { flush: 'post' });

const visiblePages = computed(() => {
    if (!props.paginatedData) return [];
    const current = props.paginatedData.page;
    const last = props.paginatedData.last_page;
    const delta = 2;
    const range: (number | string)[] = [];
    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }
    if (current - delta > 2) {
        range.unshift('...');
    }
    if (current + delta < last - 1) {
        range.push('...');
    }
    range.unshift(1);
    if (last > 1) {
        range.push(last);
    }
    return range;
});

const onGoHome = () => emit('go-home');
const onGoBack = () => emit('go-back');
const onSortAsc = () => emit('sort-asc');
const onSortDesc = () => emit('sort-desc');
const onOpenSearch = () => emit('open-search');
const onClearSearch = () => emit('clear-search');
const onToggleSelect = (path: string, item: MediaItem) => emit('toggle-select', path, item);
const onSelectFolder = (item: MediaItem) => emit('select-folder', item);
const onSelectFile = (item: MediaItem) => emit('select-file', item);
const onOpenFolder = (item: MediaItem) => emit('open-folder', item);
</script>
