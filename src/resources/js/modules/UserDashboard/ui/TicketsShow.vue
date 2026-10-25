<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Заявка #{{ submission.id }}</h1>
                <Link href="/dashboard/tickets" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                    ← К списку заявок
                </Link>
            </div>

            <div class="mb-6 flex items-center gap-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': submission.status === 'new',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': submission.status === 'in_progress',
                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': submission.status === 'completed',
                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': submission.status === 'rejected'
                }">
                    {{ submission.status_label }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formTitle || 'Форма удалена' }} · {{ formatDate(submission.created_at) }}
                </span>
            </div>

            <div v-if="fieldEntries.length > 0" class="border-t border-gray-200 dark:border-gray-700">
                <dl class="divide-y divide-gray-100 dark:divide-gray-700">
                    <div v-for="[label, value] in fieldEntries" :key="label" class="py-3 grid grid-cols-1 sm:grid-cols-3 gap-1 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ label }}</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-200 sm:col-span-2 whitespace-pre-line">{{ value }}</dd>
                    </div>
                </dl>
            </div>
            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                <p class="text-lg font-medium">Данные заявки пусты</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    currentTheme: String,
    submission: Object,
    formTitle: String,
});

const fieldEntries = computed(() => {
    const data = props.submission?.data || {};
    return Object.entries(data).map(([key, value]) => {
        const display = Array.isArray(value) ? value.join(', ') : String(value);
        return [key, display || '—'];
    });
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('ru-RU');
};
</script>
