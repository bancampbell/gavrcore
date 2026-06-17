<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Общие настройки</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <Link href="/admin/dashboard" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <div class="flex-1 flex gap-6 px-6 py-6 min-h-[calc(100vh-250px)]">
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 space-y-6">
                        <!-- Основные настройки -->
                        <div id="basic">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">Основные настройки</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Название сайта</label>
                                    <input
                                        v-model="settings.site_name"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="GavrCore CMS"
                                    />
                                </div>
                                <div class="flex items-start gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap pt-1">Описание сайта</label>
                                    <textarea
                                        v-model="settings.site_description"
                                        rows="3"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="Описание сайта для SEO"
                                    ></textarea>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Email администратора</label>
                                    <input
                                        v-model="settings.admin_email"
                                        type="email"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="admin@example.com"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Ключевые слова (SEO)</label>
                                    <input
                                        v-model="settings.seo_keywords"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="cms, laravel, vue"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Настройки материалов -->
                        <div id="materials">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">Настройки материалов</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Материалов на странице</label>
                                    <input
                                        v-model.number="settings.materials_per_page"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="w-24 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Формат даты</label>
                                    <select v-model="settings.date_format" class="w-48 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="d.m.Y">дд.мм.гггг (31.12.2024)</option>
                                        <option value="Y-m-d">гггг-мм-дд (2024-12-31)</option>
                                        <option value="m/d/Y">мм/дд/гггг (12/31/2024)</option>
                                        <option value="d/m/Y">дд/мм/гггг (31/12/2024)</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Показывать дату</label>
                                    <button
                                        @click="settings.show_date = !settings.show_date"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.show_date ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.show_date ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Показывать дату</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Показывать автора</label>
                                    <button
                                        @click="settings.show_author = !settings.show_author"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.show_author ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.show_author ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Показывать автора</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Показывать категорию</label>
                                    <button
                                        @click="settings.show_category = !settings.show_category"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.show_category ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.show_category ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Показывать категорию</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Показывать просмотры</label>
                                    <button
                                        @click="settings.show_views = !settings.show_views"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.show_views ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.show_views ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Показывать просмотры</span>
                                </div>
                            </div>
                        </div>

                        <!-- Настройки медиа -->
                        <div id="media">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">Настройки медиа</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Макс. размер файла (MB)</label>
                                    <input
                                        v-model.number="settings.max_file_size"
                                        type="number"
                                        min="1"
                                        max="50"
                                        class="w-24 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Разрешенные форматы</label>
                                    <input
                                        v-model="settings.allowed_formats"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="jpg,jpeg,png,gif,webp"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Качество изображений (%)</label>
                                    <input
                                        v-model.number="settings.image_quality"
                                        type="number"
                                        min="10"
                                        max="100"
                                        class="w-24 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Системные настройки -->
                        <div id="system">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">Системные настройки</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Режим отладки</label>
                                    <button
                                        @click="settings.debug_mode = !settings.debug_mode"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.debug_mode ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.debug_mode ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Режим отладки</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Сайт на обслуживании</label>
                                    <button
                                        @click="settings.maintenance_mode = !settings.maintenance_mode"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.maintenance_mode ? 'bg-amber-500' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.maintenance_mode ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Сайт на обслуживании</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Часовой пояс</label>
                                    <select v-model="settings.timezone" class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="UTC">UTC</option>
                                        <option value="Europe/Moscow">Москва (UTC+3)</option>
                                        <option value="Europe/London">Лондон (UTC+0)</option>
                                        <option value="America/New_York">Нью-Йорк (UTC-5)</option>
                                        <option value="Asia/Tokyo">Токио (UTC+9)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SEO настройки -->
                        <div id="seo">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">SEO настройки</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Robots</label>
                                    <input
                                        v-model="settings.robots"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="index,follow"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Google Analytics ID</label>
                                    <input
                                        v-model="settings.google_analytics_id"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="G-XXXXXXXXXX"
                                    />
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Яндекс Метрика ID</label>
                                    <input
                                        v-model="settings.yandex_metrika_id"
                                        type="text"
                                        class="flex-1 max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                        placeholder="XXXXXXXX"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Настройки кэша -->
                        <div id="cache">
                            <h2 class="text-md font-semibold text-gray-800 pb-2 border-b border-gray-200 mb-4">Настройки кэша</h2>
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Кэшировать страницы</label>
                                    <button
                                        @click="settings.cache_enabled = !settings.cache_enabled"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="settings.cache_enabled ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="settings.cache_enabled ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Кэшировать страницы</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm font-medium text-gray-700 w-48 whitespace-nowrap">Время кэша (минуты)</label>
                                    <input
                                        v-model.number="settings.cache_ttl"
                                        type="number"
                                        min="1"
                                        max="1440"
                                        class="w-24 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Боковая панель с группами настроек -->
            <div class="w-80">
                <div class="space-y-4 sticky top-24">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-sm font-semibold text-gray-800 mb-3">Быстрая навигация</h3>
                        <nav class="space-y-2">
                            <a href="#basic" @click.prevent="scrollTo('basic')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">Основные</a>
                            <a href="#materials" @click.prevent="scrollTo('materials')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">Материалы</a>
                            <a href="#media" @click.prevent="scrollTo('media')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">Медиа</a>
                            <a href="#system" @click.prevent="scrollTo('system')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">Системные</a>
                            <a href="#seo" @click.prevent="scrollTo('seo')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">SEO</a>
                            <a href="#cache" @click.prevent="scrollTo('cache')" class="block text-sm text-gray-600 hover:text-blue-600 py-1">Кэш</a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
import Toast from '@/components/shared/Toast.vue';

const props = defineProps<{
    user: any;
    title?: string;
    settings: Record<string, any>;
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const settings = ref({
    site_name: props.settings.site_name ?? 'GavrCore CMS',
    site_description: props.settings.site_description ?? '',
    admin_email: props.settings.admin_email ?? '',
    seo_keywords: props.settings.seo_keywords ?? '',
    materials_per_page: props.settings.materials_per_page ?? 10,
    date_format: props.settings.date_format ?? 'd.m.Y',
    show_date: props.settings.show_date === undefined || props.settings.show_date === null ? true : !!props.settings.show_date,
    show_author: props.settings.show_author === undefined || props.settings.show_author === null ? true : !!props.settings.show_author,
    show_category: props.settings.show_category === undefined || props.settings.show_category === null ? true : !!props.settings.show_category,
    show_views: props.settings.show_views === undefined || props.settings.show_views === null ? true : !!props.settings.show_views,
    max_file_size: props.settings.max_file_size ?? 5,
    allowed_formats: props.settings.allowed_formats ?? 'jpg,jpeg,png,gif,webp',
    image_quality: props.settings.image_quality ?? 80,
    debug_mode: props.settings.debug_mode === undefined || props.settings.debug_mode === null ? false : !!props.settings.debug_mode,
    maintenance_mode: props.settings.maintenance_mode === undefined || props.settings.maintenance_mode === null ? false : !!props.settings.maintenance_mode,
    timezone: props.settings.timezone ?? 'UTC',
    robots: props.settings.robots ?? 'index,follow',
    google_analytics_id: props.settings.google_analytics_id ?? '',
    yandex_metrika_id: props.settings.yandex_metrika_id ?? '',
    cache_enabled: props.settings.cache_enabled === undefined || props.settings.cache_enabled === null ? false : !!props.settings.cache_enabled,
    cache_ttl: props.settings.cache_ttl ?? 60,
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const scrollTo = (section: string) => {
    const element = document.getElementById(section);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
};

const save = async () => {
    loading.value = true;
    try {
        await axios.post('/admin/settings', settings.value, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        showNotification('Настройки сохранены', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    loading.value = true;
    try {
        await axios.post('/admin/settings', settings.value, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });
        router.visit('/admin/dashboard?message=Настройки+сохранены');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};
</script>
