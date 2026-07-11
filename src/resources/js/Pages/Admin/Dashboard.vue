<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="admin-dashboard px-4 lg:px-6 flex flex-col h-full">
            <!-- ЗАКРЕПЛЕННЫЙ БЛОК: Приветствие + Статистика + Заголовок активности -->
            <div class="dashboard-sticky flex-shrink-0">
                <!-- Приветствие -->
                <div class="welcome-section">
                    <div class="welcome-content">
                        <div>
                            <h1 class="welcome-title">Привет, {{ user.name }}</h1>
                            <p class="welcome-subtitle">Вот что происходит с вашим сайтом сегодня.</p>
                        </div>
                        <div class="welcome-right">
                            <div class="welcome-date">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ currentDate }}
                            </div>
                            <div class="welcome-version">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                v1.0.0
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Статистика -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-card-icon purple">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Материалы</span>
                            <span class="stat-card-value">{{ stats.materials }}</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon blue">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                            </svg>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Категории</span>
                            <span class="stat-card-value">{{ stats.categories }}</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon green">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Пользователи</span>
                            <span class="stat-card-value">{{ stats.users }}</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-card-icon orange">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-label">Просмотры</span>
                            <span class="stat-card-value">{{ stats.views }}</span>
                        </div>
                    </div>
                </div>

                <!-- Заголовок активности -->
                <div class="activity-header">
                    <h3 class="activity-title">Последняя активность</h3>
                    <button
                        v-if="activities.data?.length > 0"
                        @click="loadMore"
                        class="activity-button"
                    >
                        Показать всё
                    </button>
                </div>
            </div>

            <!-- СКРОЛЛИМЫЙ БЛОК: список событий -->
            <div class="activity-list-wrapper flex-1 overflow-y-auto">
                <div v-if="activities.data?.length === 0" class="text-center py-8 text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p>Пока нет активности</p>
                    <p class="text-sm mt-1">Начните создавать материалы или пользователи будут регистрироваться</p>
                </div>

                <div v-else class="space-y-1">
                    <div
                        v-for="activity in activities.data"
                        :key="activity.id"
                        class="flex items-start gap-3 py-3 border-b border-slate-100 last:border-0"
                    >
                        <div
                            class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0"
                            :class="{
                                'bg-emerald-400': getDotColor(activity) === 'green',
                                'bg-blue-400': getDotColor(activity) === 'blue',
                                'bg-purple-400': getDotColor(activity) === 'purple',
                                'bg-red-400': getDotColor(activity) === 'red',
                                'bg-slate-400': getDotColor(activity) === 'gray',
                            }"
                        ></div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center flex-wrap gap-1">
                                    <span class="text-sm text-slate-700">
                                        {{ getEventText(activity) }}
                                        <span v-if="getSubjectName(activity)" class="font-medium text-[#3071a9]">
                                            "{{ getSubjectName(activity) }}"
                                        </span>
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        {{ getCauserName(activity) }}
                                    </span>
                                </div>
                                <span class="text-xs text-slate-400 whitespace-nowrap">
                                    {{ formatTime(activity.created_at) }}
                                </span>
                            </div>
                            <div v-if="activity.properties && activity.properties.attributes" class="text-xs text-slate-500 mt-0.5">
                                <span v-for="(value, key) in getChangedFields(activity)" :key="key" class="mr-3">
                                    <span class="font-medium">{{ key }}</span>:
                                    <span class="text-slate-600">{{ value }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ФИКСИРОВАННАЯ ПАГИНАЦИЯ (футер) -->
            <div v-if="activities.data?.length > 0" class="dashboard-pagination flex-shrink-0">
                <div class="admin-pagination">
                    <div class="admin-pagination-info">
                        Показано {{ activities.from || 0 }} - {{ activities.to || 0 }} из {{ activities.total || 0 }}
                    </div>
                    <div class="admin-pagination-controls">
                        <button
                            @click="prevPage"
                            :disabled="activities.current_page === 1"
                            class="admin-pagination-btn"
                        >
                            ← Назад
                        </button>
                        <span class="admin-pagination-current">
                            {{ activities.current_page }} / {{ activities.last_page }}
                        </span>
                        <button
                            @click="nextPage"
                            :disabled="activities.current_page === activities.last_page"
                            class="admin-pagination-btn"
                        >
                            Вперед →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    user: any;
    stats: any;
    activities: any;
    title: string;
}>();

const currentDate = computed(() => {
    return new Date().toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

const formatTime = (date: string) => {
    const now = new Date();
    const past = new Date(date);
    const diff = Math.floor((now.getTime() - past.getTime()) / 1000);

    if (diff < 60) return 'только что';
    if (diff < 3600) return Math.floor(diff / 60) + ' минут назад';
    if (diff < 86400) return Math.floor(diff / 3600) + ' часов назад';
    return past.toLocaleDateString('ru-RU', { day: 'numeric', month: 'short' });
};

const getDotColor = (activity: any) => {
    const description = activity.description || '';
    if (description.includes('Создан') || description.includes('Зарегистрирован')) return 'green';
    if (description.includes('Обновлен')) return 'blue';
    if (description.includes('Удален')) return 'red';
    if (description.includes('Восстановлен')) return 'green';
    if (description.includes('Вход')) return 'purple';
    if (description.includes('Выход')) return 'gray';
    return 'blue';
};

const getEventText = (activity: any) => {
    return activity.description || 'Событие';
};

const getCauserName = (activity: any) => {
    if (activity.causer) {
        return activity.causer.name || activity.causer.email || 'Пользователь';
    }
    return 'Система';
};

const getSubjectName = (activity: any) => {
    if (!activity.subject) return null;

    if (activity.subject_type === 'App\\Models\\Material') {
        return activity.subject.title || 'Материал #' + activity.subject.id;
    }

    if (activity.subject_type === 'App\\Models\\User') {
        return activity.subject.name || activity.subject.email || 'Пользователь #' + activity.subject.id;
    }

    return null;
};

const getChangedFields = (activity: any) => {
    const props = activity.properties || {};
    const attributes = props.attributes || {};
    const old = props.old || {};

    const changed: Record<string, string> = {};
    for (const key in attributes) {
        if (old[key] !== attributes[key]) {
            changed[key] = attributes[key];
        }
    }
    return changed;
};

const loadMore = () => {
    router.get('/admin/dashboard', { page: 1 }, { preserveState: true });
};

const prevPage = () => {
    if (props.activities.current_page > 1) {
        router.get('/admin/dashboard', { page: props.activities.current_page - 1 }, { preserveState: true });
    }
};

const nextPage = () => {
    if (props.activities.current_page < props.activities.last_page) {
        router.get('/admin/dashboard', { page: props.activities.current_page + 1 }, { preserveState: true });
    }
};
</script>
