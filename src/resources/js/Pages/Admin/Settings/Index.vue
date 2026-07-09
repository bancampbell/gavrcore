<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="settings-page">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Общие настройки</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="save"
                        :disabled="loading"
                        class="admin-btn admin-btn-primary"
                    >
                        Сохранить
                    </button>
                    <button
                        @click="saveAndClose"
                        :disabled="loading"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сохранить и закрыть
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Отменить
                    </button>
                </div>
            </div>

            <!-- Основной контент с сайдбаром справа -->
            <div class="admin-page-content-with-sidebar">
                <!-- Левая часть - контент -->
                <div class="admin-page-content-scroll" ref="contentContainer">
                    <div class="admin-page-card w-full p-6">
                        <!-- Основные настройки -->
                        <div id="basic" ref="basicSection">
                            <h2 class="admin-form-section-title">Основные настройки</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Название сайта</label>
                                    <input
                                        v-model="settings.site_name"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="GavrCore CMS"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48 pt-1">Описание сайта</label>
                                    <textarea
                                        v-model="settings.site_description"
                                        rows="3"
                                        class="admin-form-textarea flex-1 max-w-md"
                                        placeholder="Описание сайта для SEO"
                                    ></textarea>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Email администратора</label>
                                    <input
                                        v-model="settings.admin_email"
                                        type="email"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="admin@example.com"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Ключевые слова (SEO)</label>
                                    <input
                                        v-model="settings.seo_keywords"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="cms, laravel, vue"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Настройки материалов -->
                        <div id="materials" ref="materialsSection" class="admin-form-section">
                            <h2 class="admin-form-section-title">Настройки материалов</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Материалов на странице</label>
                                    <input
                                        v-model.number="settings.materials_per_page"
                                        type="number"
                                        min="1"
                                        max="100"
                                        class="admin-form-input w-24"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Формат даты</label>
                                    <select v-model="settings.date_format" class="admin-form-select w-48">
                                        <option value="d.m.Y">дд.мм.гггг (31.12.2024)</option>
                                        <option value="Y-m-d">гггг-мм-дд (2024-12-31)</option>
                                        <option value="m/d/Y">мм/дд/гггг (12/31/2024)</option>
                                        <option value="d/m/Y">дд/мм/гггг (31/12/2024)</option>
                                    </select>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Показывать дату</label>
                                    <button
                                        @click="settings.show_date = !settings.show_date"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.show_date ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.show_date ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Показывать дату</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Показывать автора</label>
                                    <button
                                        @click="settings.show_author = !settings.show_author"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.show_author ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.show_author ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Показывать автора</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Показывать категорию</label>
                                    <button
                                        @click="settings.show_category = !settings.show_category"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.show_category ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.show_category ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Показывать категорию</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Показывать просмотры</label>
                                    <button
                                        @click="settings.show_views = !settings.show_views"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.show_views ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.show_views ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Показывать просмотры</span>
                                </div>
                            </div>
                        </div>

                        <!-- Настройки медиа -->
                        <div id="media" ref="mediaSection" class="admin-form-section">
                            <h2 class="admin-form-section-title">Настройки медиа</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Макс. размер файла (MB)</label>
                                    <input
                                        v-model.number="settings.max_file_size"
                                        type="number"
                                        min="1"
                                        max="50"
                                        class="admin-form-input w-24"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Разрешенные форматы</label>
                                    <input
                                        v-model="settings.allowed_formats"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="jpg,jpeg,png,gif,webp"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Качество изображений (%)</label>
                                    <input
                                        v-model.number="settings.image_quality"
                                        type="number"
                                        min="10"
                                        max="100"
                                        class="admin-form-input w-24"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Системные настройки -->
                        <div id="system" ref="systemSection" class="admin-form-section">
                            <h2 class="admin-form-section-title">Системные настройки</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Режим отладки</label>
                                    <button
                                        @click="settings.debug_mode = !settings.debug_mode"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.debug_mode ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.debug_mode ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Режим отладки</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Сайт на обслуживании</label>
                                    <button
                                        @click="settings.maintenance_mode = !settings.maintenance_mode"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.maintenance_mode ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.maintenance_mode ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Сайт на обслуживании</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Часовой пояс</label>
                                    <select v-model="settings.timezone" class="admin-form-select w-64">
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
                        <div id="seo" ref="seoSection" class="admin-form-section">
                            <h2 class="admin-form-section-title">SEO настройки</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Robots</label>
                                    <input
                                        v-model="settings.robots"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="index,follow"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Google Analytics ID</label>
                                    <input
                                        v-model="settings.google_analytics_id"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="G-XXXXXXXXXX"
                                    />
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Яндекс Метрика ID</label>
                                    <input
                                        v-model="settings.yandex_metrika_id"
                                        type="text"
                                        class="admin-form-input flex-1 max-w-md"
                                        placeholder="XXXXXXXX"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Настройки кэша -->
                        <div id="cache" ref="cacheSection" class="admin-form-section">
                            <h2 class="admin-form-section-title">Настройки кэша</h2>
                            <div class="admin-form-grid">
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Кэшировать страницы</label>
                                    <button
                                        @click="settings.cache_enabled = !settings.cache_enabled"
                                        type="button"
                                        class="admin-toggle"
                                        :class="settings.cache_enabled ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span class="admin-toggle-slider" :class="settings.cache_enabled ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                                    </button>
                                    <span class="admin-toggle-label">Кэшировать страницы</span>
                                </div>
                                <div class="admin-form-row">
                                    <label class="admin-form-label w-48">Время кэша (минуты)</label>
                                    <input
                                        v-model.number="settings.cache_ttl"
                                        type="number"
                                        min="1"
                                        max="1440"
                                        class="admin-form-input w-24"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Правая часть - сайдбар с быстрой навигацией -->
                <div class="admin-sidebar-wrapper">
                    <div class="admin-sidebar-card">
                        <h3 class="admin-sidebar-title">Быстрая навигация</h3>
                        <nav class="admin-sidebar-nav">
                            <a
                                v-for="item in navItems"
                                :key="item.id"
                                href="#"
                                @click.prevent="scrollTo(item.id)"
                                class="admin-sidebar-link"
                                :class="{ 'admin-sidebar-link-active': activeSection === item.id }"
                            >
                                {{ item.label }}
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';

const props = defineProps<{
    user: any;
    title?: string;
    settings: Record<string, any>;
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const activeSection = ref('basic');

const navItems = [
    { id: 'basic', label: 'Основные' },
    { id: 'materials', label: 'Материалы' },
    { id: 'media', label: 'Медиа' },
    { id: 'system', label: 'Системные' },
    { id: 'seo', label: 'SEO' },
    { id: 'cache', label: 'Кэш' },
];

const contentContainer = ref<HTMLElement | null>(null);
const basicSection = ref<HTMLElement | null>(null);
const materialsSection = ref<HTMLElement | null>(null);
const mediaSection = ref<HTMLElement | null>(null);
const systemSection = ref<HTMLElement | null>(null);
const seoSection = ref<HTMLElement | null>(null);
const cacheSection = ref<HTMLElement | null>(null);

const sectionRefs: Record<string, any> = {
    basic: basicSection,
    materials: materialsSection,
    media: mediaSection,
    system: systemSection,
    seo: seoSection,
    cache: cacheSection,
};

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

const scrollTo = (sectionId: string) => {
    activeSection.value = sectionId;
    const ref = sectionRefs[sectionId];
    if (ref && ref.value) {
        ref.value.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
};

// Отслеживаем активную секцию при скролле
const handleScroll = () => {
    const container = contentContainer.value;
    if (!container) return;

    const sections = navItems.map(item => ({
        id: item.id,
        el: sectionRefs[item.id]?.value
    }));

    let currentSection = 'basic';
    let minDistance = Infinity;

    sections.forEach(({ id, el }) => {
        if (!el) return;
        const rect = el.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();
        const distance = Math.abs(rect.top - containerRect.top);

        if (distance < minDistance && rect.top >= containerRect.top - 100) {
            minDistance = distance;
            currentSection = id;
        }
    });

    activeSection.value = currentSection;
};

const cancel = () => {
    router.visit('/admin/dashboard');
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

onMounted(() => {
    const container = contentContainer.value;
    if (container) {
        container.addEventListener('scroll', handleScroll);
    }
});

onUnmounted(() => {
    const container = contentContainer.value;
    if (container) {
        container.removeEventListener('scroll', handleScroll);
    }
});
</script>
