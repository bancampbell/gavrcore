<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Темы оформления</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        v-if="selectedTheme !== currentTheme"
                        @click="saveTheme"
                        :disabled="saving"
                        class="admin-btn admin-btn-primary"
                    >
                        {{ saving ? 'Применение...' : 'Применить тему' }}
                    </button>
                    <button
                        v-else
                        disabled
                        class="admin-btn admin-btn-secondary opacity-50 cursor-not-allowed"
                    >
                        Тема активна
                    </button>
                    <button
                        @click="resetToCurrent"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сбросить
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Назад
                    </button>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-6">
                    <!-- Текущая тема -->
                    <div class="admin-theme-current mb-6">
                        <div class="admin-theme-current-icon">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                        </div>
                        <div>
                            <div class="admin-theme-current-label">Активная тема</div>
                            <div class="admin-theme-current-name">{{ currentThemeName }}</div>
                        </div>
                    </div>

                    <!-- Список тем -->
                    <div class="admin-theme-list">
                        <div
                            v-for="theme in themes"
                            :key="theme.id"
                            class="admin-theme-item"
                            :class="{
                                'admin-theme-item-active': selectedTheme === theme.id,
                                'admin-theme-item-current': currentTheme === theme.id
                            }"
                            @click="selectTheme(theme.id)"
                        >
                            <!-- Превью -->
                            <div
                                class="admin-theme-preview"
                                :style="{
                                    background: `linear-gradient(135deg, ${theme.colors?.primary || '#4f46e5'}, ${theme.colors?.secondary || '#7c3aed'})`
                                }"
                            >
                                <div class="admin-theme-preview-inner">
                                    <div class="admin-theme-preview-bar" :style="{ background: theme.colors?.surface || '#ffffff', opacity: 0.7 }"></div>
                                    <div class="admin-theme-preview-bar-short" :style="{ background: theme.colors?.surface || '#ffffff', opacity: 0.4 }"></div>
                                </div>
                            </div>

                            <!-- Информация -->
                            <div class="admin-theme-info">
                                <div class="admin-theme-header">
                                    <h3 class="admin-theme-name">{{ theme.name }}</h3>
                                    <span v-if="currentTheme === theme.id" class="admin-theme-badge admin-theme-badge-active">
                                        Активно
                                    </span>
                                    <span v-if="selectedTheme === theme.id && currentTheme !== theme.id" class="admin-theme-badge admin-theme-badge-selected">
                                        Выбрано
                                    </span>
                                </div>
                                <p class="admin-theme-description">{{ theme.description }}</p>
                                <div class="admin-theme-colors">
                                    <div class="admin-theme-color" :style="{ background: theme.colors?.primary || '#4f46e5' }"></div>
                                    <div class="admin-theme-color" :style="{ background: theme.colors?.secondary || '#7c3aed' }"></div>
                                    <div class="admin-theme-color" :style="{ background: theme.colors?.bg || '#f9fafb' }"></div>
                                    <div class="admin-theme-color" :style="{ background: theme.colors?.surface || '#ffffff' }"></div>
                                </div>
                            </div>

                            <!-- Кнопка -->
                            <div class="admin-theme-actions">
                                <button
                                    v-if="selectedTheme === theme.id && currentTheme !== theme.id"
                                    @click.stop="saveTheme"
                                    :disabled="saving"
                                    class="admin-btn admin-btn-primary admin-btn-sm"
                                >
                                    {{ saving ? '...' : 'Применить' }}
                                </button>
                                <button
                                    v-else-if="selectedTheme === theme.id && currentTheme === theme.id"
                                    disabled
                                    class="admin-btn admin-btn-secondary admin-btn-sm opacity-50 cursor-not-allowed"
                                >
                                    Активно
                                </button>
                                <button
                                    v-else
                                    @click.stop="selectTheme(theme.id)"
                                    class="admin-btn admin-btn-secondary admin-btn-sm"
                                >
                                    Выбрать
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';

interface Theme {
    id: string;
    name: string;
    description: string;
    colors?: {
        primary: string;
        secondary: string;
        bg: string;
        surface: string;
    };
}

interface Props {
    user: any;
    title?: string;
    currentTheme: string;
    themes: Theme[];
}

const props = defineProps<Props>();

const selectedTheme = ref(props.currentTheme);
const saving = ref(false);

const notification = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error'
});

const currentThemeName = computed(() => {
    const theme = props.themes.find(t => t.id === props.currentTheme);
    return theme ? theme.name : props.currentTheme;
});

const selectTheme = (themeId: string) => {
    if (saving.value) return;
    selectedTheme.value = themeId;
};

const saveTheme = () => {
    if (saving.value || selectedTheme.value === props.currentTheme) return;

    saving.value = true;

    router.post('/admin/themes', {
        theme: selectedTheme.value
    }, {
        onSuccess: () => {
            notification.value = {
                show: true,
                message: 'Тема успешно применена!',
                type: 'success'
            };
            saving.value = false;

            document.documentElement.setAttribute('data-theme', selectedTheme.value);

            setTimeout(() => {
                notification.value.show = false;
                window.location.reload();
            }, 1500);
        },
        onError: () => {
            notification.value = {
                show: true,
                message: 'Ошибка при применении темы',
                type: 'error'
            };
            saving.value = false;

            setTimeout(() => {
                notification.value.show = false;
            }, 3000);
        }
    });
};

const resetToCurrent = () => {
    selectedTheme.value = props.currentTheme;
    notification.value = {
        show: true,
        message: 'Сброшено к текущей теме',
        type: 'success'
    };

    setTimeout(() => {
        notification.value.show = false;
    }, 2000);
};

const cancel = () => {
    router.visit('/admin/dashboard');
};

onMounted(() => {
    document.documentElement.setAttribute('data-theme', props.currentTheme);
});
</script>
