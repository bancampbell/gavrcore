<template>
    <div class="grid gap-4 px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
         :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
         style="grid-template-columns: 40px 60px minmax(200px, 1fr) minmax(150px, 1.5fr) 140px">

        <div class="flex items-center justify-center">
            <input
                type="checkbox"
                :checked="selected"
                @change="$emit('toggle-select', item.id)"
                class="rounded border-gray-300"
            />
        </div>

        <div class="flex items-center justify-center text-gray-600">{{ item.id }}</div>

        <div class="flex items-start" :style="{ paddingLeft: `${level * 20}px` }">
            <div>
                <button
                    @click="$emit('edit', item.id)"
                    class="font-medium text-[#3071a9] hover:underline text-left"
                >
                    {{ item.title }}
                </button>
                <div class="text-xs text-gray-400 mt-0.5">
                    {{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}
                </div>
            </div>
        </div>

        <div class="flex items-center">
            <span :class="getMenuColorClass(item.menu_type?.title)">
                {{ item.menu_type?.title || '—' }}
            </span>
        </div>

        <div class="flex items-center justify-center">
            <span
                @click="$emit('toggle-status', item.id, !item.status)"
                :class="[
                    'px-2 py-1 text-xs rounded-full font-medium cursor-pointer transition whitespace-nowrap',
                    item.status ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-700 hover:bg-red-200'
                ]"
            >
                {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { MenuItem } from '@/api/menu';

defineProps<{
    item: MenuItem & { level?: number; menu_type?: { title: string } };
    level: number;
    selected: boolean;
    index?: number;
}>();

defineEmits<{
    (e: 'toggle-select', id: number): void;
    (e: 'edit', id: number): void;
    (e: 'toggle-status', id: number, status: boolean): void;
}>();

const getLinkTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        url: 'URL',
        material: 'Материал',
        separator: 'Разделитель',
        heading: 'Заголовок',
        external: 'Внешний URL'
    };
    return labels[type] || type;
};

const getLinkValueDisplay = (type: string, value: string | null) => {
    if (!value) return '—';
    if (type === 'material') return `ID: ${value}`;
    if (type === 'external' || type === 'url') return value;
    return value;
};

const getMenuColorClass = (menuTitle?: string) => {
    const colors: Record<string, string> = {
        'Main Menu': 'px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700',
        'Menu login': 'px-2 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-700',
        'Nevidimia': 'px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700',
    };

    if (menuTitle && colors[menuTitle]) {
        return colors[menuTitle];
    }

    const hash = menuTitle ? menuTitle.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0) : 0;
    const colorClasses = [
        'px-2 py-1 text-xs rounded-full font-medium bg-emerald-100 text-emerald-700',
        'px-2 py-1 text-xs rounded-full font-medium bg-amber-100 text-amber-700',
        'px-2 py-1 text-xs rounded-full font-medium bg-cyan-100 text-cyan-700',
        'px-2 py-1 text-xs rounded-full font-medium bg-rose-100 text-rose-700',
        'px-2 py-1 text-xs rounded-full font-medium bg-indigo-100 text-indigo-700',
        'px-2 py-1 text-xs rounded-full font-medium bg-teal-100 text-teal-700',
    ];
    return colorClasses[hash % colorClasses.length];
};
</script>
