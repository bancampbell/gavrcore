<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="max-w-4xl mx-auto">
            <!-- Заголовок -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Темы оформления</h1>
                <p class="text-gray-500 mt-1">Выберите тему для всего сайта. Изменения применяются мгновенно.</p>
            </div>

            <!-- Текущая тема (упрощённо) -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-4 mb-8 border border-blue-100">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Активная тема</div>
                        <div class="text-lg font-semibold text-gray-800">{{ currentThemeName }}</div>
                    </div>
                </div>
            </div>

            <!-- Список тем ВЕРТИКАЛЬНО -->
            <div class="space-y-4">
                <div
                    v-for="theme in themes"
                    :key="theme.id"
                    class="bg-white rounded-xl border-2 transition-all cursor-pointer hover:shadow-md"
                    :class="[
                        selectedTheme === theme.id
                            ? 'border-blue-500 shadow-md'
                            : 'border-gray-200 hover:border-blue-300'
                    ]"
                    @click="selectTheme(theme.id)"
                >
                    <div class="flex items-center p-4 gap-4">
                        <!-- Превью -->
                        <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0" :style="{
                            background: `linear-gradient(135deg, ${theme.colors?.primary || '#4f46e5'}, ${theme.colors?.secondary || '#7c3aed'})`
                        }">
                            <div class="w-full h-full flex items-end p-2">
                                <div class="w-full">
                                    <div class="h-1.5 rounded w-full" :style="{ background: theme.colors?.surface || '#ffffff', opacity: 0.7 }"></div>
                                    <div class="h-1 rounded w-2/3 mt-1" :style="{ background: theme.colors?.surface || '#ffffff', opacity: 0.4 }"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3">
                                <h3 class="font-semibold text-gray-800 text-lg">{{ theme.name }}</h3>
                                <span v-if="currentTheme === theme.id" class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-medium rounded-full flex-shrink-0">
                                    Активно
                                </span>
                                <span v-if="selectedTheme === theme.id && currentTheme !== theme.id" class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full flex-shrink-0">
                                    Выбрано
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5">{{ theme.description }}</p>
                            <div class="flex gap-1 mt-2">
                                <div class="h-1.5 w-8 rounded" :style="{ background: theme.colors?.primary || '#4f46e5' }"></div>
                                <div class="h-1.5 w-8 rounded" :style="{ background: theme.colors?.secondary || '#7c3aed' }"></div>
                                <div class="h-1.5 w-8 rounded" :style="{ background: theme.colors?.bg || '#f9fafb' }"></div>
                                <div class="h-1.5 w-8 rounded" :style="{ background: theme.colors?.surface || '#ffffff' }"></div>
                            </div>
                        </div>

                        <!-- Кнопка -->
                        <div class="flex-shrink-0">
                            <button
                                v-if="selectedTheme === theme.id && currentTheme !== theme.id"
                                @click.stop="saveTheme"
                                :disabled="saving"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50"
                            >
                                {{ saving ? 'Применение...' : 'Применить' }}
                            </button>
                            <button
                                v-else-if="selectedTheme === theme.id && currentTheme === theme.id"
                                disabled
                                class="px-6 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed"
                            >
                                Активно
                            </button>
                            <button
                                v-else
                                @click.stop="selectTheme(theme.id)"
                                class="px-6 py-2 border border-gray-300 hover:border-blue-400 text-gray-600 hover:text-blue-600 text-sm font-medium rounded-lg transition"
                            >
                                Выбрать
                            </button>
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

onMounted(() => {
    document.documentElement.setAttribute('data-theme', props.currentTheme);
});
</script>
