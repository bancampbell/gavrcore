<template>
    <div
        :data-file-path="!isFolder ? item.path : undefined"
        class="flex items-center py-1 px-2 rounded-md transition-colors hover:bg-[#e6f0fa] group cursor-pointer"
        :class="{ 'bg-[#e6f0fa]': isSelected }"
        @click="onClick"
    >
        <input
            type="checkbox"
            :checked="isSelected"
            @change="onSelect"
            @click.stop
            class="mr-2 w-3.5 h-3.5 rounded border-gray-300 cursor-pointer"
        />
        <div class="flex items-center flex-1 min-w-0">
            <svg
                v-if="isFolder"
                class="w-5 h-5 mr-2 flex-shrink-0"
                fill="#D2B073"
                stroke="#D2B073"
                stroke-width="1.5"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            <span v-else class="text-base mr-2 flex-shrink-0">{{ getFileIcon(item.name) }}</span>
            <span
                class="text-sm truncate"
                :class="isSelected ? 'text-[#333] font-medium' : 'text-gray-700'"
            >
                {{ item.name }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getFileIcon } from '../constants';

interface MediaItem {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

const props = defineProps<{
    item: MediaItem;
    isSelected: boolean;
    isFolder: boolean;
}>();

const emit = defineEmits<{
    (e: 'select', path: string, item: MediaItem): void;
    (e: 'click', item: MediaItem): void;
}>();

const onSelect = () => {
    emit('select', props.item.path, props.item);
};

const onClick = () => {
    emit('click', props.item);
};
</script>
