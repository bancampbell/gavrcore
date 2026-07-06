<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="admin-dashboard px-4 lg:px-6">
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
                        <span class="stat-card-change positive">+12%</span>
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
                        <span class="stat-card-change positive">+4</span>
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
                        <span class="stat-card-change">+2</span>
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
                        <span class="stat-card-change positive">+18%</span>
                    </div>
                </div>
            </div>

            <!-- Активность -->
            <div class="activity-section">
                <div class="activity-header">
                    <div>
                        <h3 class="activity-title">Последняя активность</h3>
                        <p class="activity-subtitle">Что происходит на сайте за последние 24 часа</p>
                        <p class="activity-subtitle">Что происходит на сайте за последние 24 часа</p>
                        <p class="activity-subtitle">Что происходит на сайте за последние 24 часа</p>


                    </div>
                    <button class="activity-button">Показать всё</button>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-dot green"></div>
                        <div class="activity-content">
                            <span class="activity-text">Новый материал опубликован</span>
                            <span class="activity-time">5 минут назад</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-dot blue"></div>
                        <div class="activity-content">
                            <span class="activity-text">Пользователь зарегистрировался</span>
                            <span class="activity-time">2 часа назад</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-dot purple"></div>
                        <div class="activity-content">
                            <span class="activity-text">Добавлена новая категория</span>
                            <span class="activity-time">5 часов назад</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import {computed} from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import {Head} from '@inertiajs/vue3';

defineProps({
    user: Object,
    stats: Object,
    title: String,
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});
</script>
