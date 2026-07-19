<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Мои заявки</h1>
                <Link href="/dashboard/tickets/new" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 border border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-300 text-sm font-medium rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Новая заявка
                </Link>
            </div>

            <div v-if="submissions.data && submissions.data.length > 0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Дата</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Форма</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Статус</th>
                            <th class="text-left py-3 px-4 font-medium text-gray-500 dark:text-gray-400">Действие</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in submissions.data" :key="item.id" class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-300">
                                {{ formatDate(item.created_at) }}
                            </td>
                            <td class="py-3 px-4 text-gray-800 dark:text-gray-200">
                                {{ item.form?.title || 'Без названия' }}
                            </td>
                            <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': item.status === 'new',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': item.status === 'in_progress',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': item.status === 'completed',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': item.status === 'rejected'
                                    }">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                            </td>
                            <td class="py-3 px-4">
                                <Link :href="`/dashboard/tickets/${item.id}`" class="text-indigo-700 dark:text-indigo-300 hover:underline text-sm font-medium">
                                    Подробнее →
                                </Link>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Показано {{ submissions.from || 0 }} - {{ submissions.to || 0 }} из {{ submissions.total || 0 }}
                    </span>
                    <div class="flex gap-2">
                        <button
                            @click="prevPage"
                            :disabled="submissions.current_page === 1"
                            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded disabled:opacity-50 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            ←
                        </button>
                        <span class="px-3 py-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ submissions.current_page }} / {{ submissions.last_page }}
                        </span>
                        <button
                            @click="nextPage"
                            :disabled="submissions.current_page === submissions.last_page"
                            class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded disabled:opacity-50 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            →
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-lg font-medium">У вас пока нет заявок</p>
                <p class="text-sm mt-1">Создайте свою первую заявку</p>
                <Link href="/dashboard/tickets/new" class="inline-block mt-4 px-4 py-2 bg-white dark:bg-gray-700 border border-indigo-600 dark:border-indigo-400 text-indigo-600 dark:text-indigo-300 text-sm font-medium rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors">
                    Создать заявку
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3';
import DashboardLayout from '@/layouts/DashboardLayout.vue';

const props = defineProps({
    user: Object,
    currentTheme: String,
    submissions: Object,
});

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusLabel = (status) => {
    const map = {
        new: 'Новая',
        in_progress: 'В работе',
        completed: 'Завершена',
        rejected: 'Отклонена',
    };
    return map[status] || status;
};

const prevPage = () => {
    if (props.submissions.current_page > 1) {
        router.get('/dashboard/tickets?page=' + (props.submissions.current_page - 1));
    }
};

const nextPage = () => {
    if (props.submissions.current_page < props.submissions.last_page) {
        router.get('/dashboard/tickets?page=' + (props.submissions.current_page + 1));
    }
};
</script>
