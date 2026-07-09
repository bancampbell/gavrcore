<!-- resources/js/Pages/Admin/MediaManager/components/ContentPanel.vue -->
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

        <div class="flex-1 overflow-y-auto p-2" ref="scrollContainer">
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>
            <div v-else class="space-y-0.5">
                <FileItem
                    v-for="item in folders"
                    :key="item.path"
                    :item="item"
                    :is-selected="isSelected(item.path)"
                    :is-folder="true"
                    :is-picker-mode="mode === 'picker'"
                    @select="onToggleSelect"
                    @click="onSelectFolder"
                    @dblclick="onFolderDoubleClick"
                />
                <FileItem
                    v-for="item in files"
                    :key="item.path"
                    :item="item"
                    :is-selected="isSelected(item.path)"
                    :is-folder="false"
                    :is-picker-mode="mode === 'picker'"
                    :data-file-path="item.path"
                    @select="onToggleSelect"
                    @click="onSelectFile"
                />
            </div>
            <div v-if="!loading && folders.length === 0 && files.length === 0 && !showSearch" class="text-center py-12 text-gray-400">
                Папка пуста
            </div>
            <div v-if="showSearch && folders.length === 0 && files.length === 0 && !loading" class="text-center py-12 text-gray-400">
                Ничего не найдено
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
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
}>();

const scrollContainer = ref<HTMLDivElement | null>(null);

const onGoHome = () => emit('go-home');
const onGoBack = () => emit('go-back');
const onSortAsc = () => emit('sort-asc');
const onSortDesc = () => emit('sort-desc');
const onOpenSearch = () => emit('open-search');
const onClearSearch = () => emit('clear-search');
const onToggleSelect = (path: string, item: MediaItem) => emit('toggle-select', path, item);
const onSelectFolder = (item: MediaItem) => emit('select-folder', item);
const onSelectFile = (item: MediaItem) => emit('select-file', item);
const onFolderDoubleClick = (item: MediaItem) => emit('open-folder', item);

const scrollToFile = (filePath: string) => {
    if (!scrollContainer.value) return;

    const elements = scrollContainer.value.querySelectorAll('[data-file-path]');
    let targetElement: HTMLElement | null = null;

    for (let i = 0; i < elements.length; i++) {
        const el = elements[i] as HTMLElement;
        if (el.getAttribute('data-file-path') === filePath) {
            targetElement = el;
            break;
        }
    }

    if (targetElement) {
        targetElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    }
};

defineExpose({
    scrollToFile
});
</script>
