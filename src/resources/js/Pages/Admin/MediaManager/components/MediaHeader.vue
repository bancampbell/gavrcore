<!-- resources/js/Pages/Admin/MediaManager/components/MediaHeader.vue -->

<template>
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4">
        </div>

        <div class="px-6 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm">
                <Link href="/admin/dashboard">
                    <svg class="w-4 h-4 text-gray-500 hover:text-blue-600 cursor-pointer transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </Link>
                <span class="text-gray-600">{{ currentPathDisplay }}</span>
                <span class="text-gray-400">({{ foldersCount }} папок, {{ filesCount }} файлов)</span>
            </div>

            <div class="flex gap-2">
                <button @click="onCreateFolder" class="rounded transition p-0.5 flex items-center gap-2" title="Новая папка">
                    <svg class="w-8 h-8 transition-colors" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <span class="text-sm text-gray-700">Новая папка</span>
                </button>
                <button @click="onUpload" class="p-1.5 rounded transition flex items-center gap-2 group" title="Загрузить">
                    <svg class="w-5 h-5 text-gray-600 transition-colors group-hover:text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="text-sm text-gray-700">Загрузить</span>
                </button>

                <button class="p-1.5 rounded transition flex items-center gap-2 group" title="Справка">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" class="transition-colors group-hover:stroke-amber-700 group-hover:fill-amber-100" />
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3" class="transition-colors group-hover:stroke-amber-700" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <line x1="12" y1="17" x2="12.01" y2="17" class="transition-colors group-hover:stroke-amber-700" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="text-sm text-gray-700">Справка</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    currentPathDisplay: string;
    foldersCount: number;
    filesCount: number;
}>();

const emit = defineEmits<{
    (e: 'create-folder'): void;
    (e: 'upload'): void;
}>();

const onCreateFolder = () => emit('create-folder');
const onUpload = () => emit('upload');
</script>
