<template>
    <DashboardLayout>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Обзор</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Всего заявок</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalTickets }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">В обработке</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ inProgressTickets }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Завершено</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ completedTickets }}</p>
                </div>
            </div>

            <div class="mt-8 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center text-gray-500 dark:text-gray-400">
                <p>Здесь будет ваша статистика и активность</p>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/layouts/DashboardLayout.vue';

defineProps({
    user: Object,
    currentTheme: String,
    totalTickets: Number,
    inProgressTickets: Number,
    completedTickets: Number,
});
</script>
