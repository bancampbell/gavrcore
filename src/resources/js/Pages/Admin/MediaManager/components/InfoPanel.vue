<!-- resources/js/Pages/Admin/MediaManager/components/InfoPanel.vue -->

<template>
    <div class="w-72 border-l border-gray-200 bg-white flex flex-col overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-200 bg-gray-50 text-sm font-medium text-gray-600">
            Подробная информация
        </div>
        <div class="flex-1 overflow-y-auto p-3">
            <div v-if="selectedItem" class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-800 text-sm break-all">{{ selectedItem.name }}</h3>
                    <p class="text-xs text-gray-500 mt-1">{{ selectedItem.type === 'folder' ? 'Папка' : 'Файл' }}</p>
                    <p v-if="selectedItem.modified" class="text-xs text-gray-400 mt-1">Изменено: {{ formatDate(selectedItem.modified) }}</p>
                    <p v-if="selectedItem.type === 'file' && selectedItem.size" class="text-xs text-gray-400">Размер: {{ formatFileSize(selectedItem.size) }}</p>
                </div>
                <div class="flex gap-2 pt-3 border-t border-gray-200">
                    <button @click="onDelete" class="p-1.5 rounded hover:bg-gray-100 transition" title="Удалить">
                        <svg class="w-4 h-4 text-gray-500 hover:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                    <button @click="onRename" class="p-1.5 rounded hover:bg-gray-100 transition" title="Переименовать">
                        <svg class="w-4 h-4 text-gray-500 hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button @click="onCopy" class="p-1.5 rounded hover:bg-gray-100 transition" title="Копировать">
                        <svg class="w-4 h-4 text-gray-500 hover:text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>
            <div v-else class="text-center text-gray-400 py-12">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">Выберите файл или папку</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { formatDate, formatFileSize } from '../constants';
import type { MediaItem } from '../types';

defineProps<{
    selectedItem: MediaItem | null;
}>();

const emit = defineEmits<{
    (e: 'delete'): void;
    (e: 'rename'): void;
    (e: 'copy'): void;
}>();

const onDelete = () => emit('delete');
const onRename = () => emit('rename');
const onCopy = () => emit('copy');
</script>
