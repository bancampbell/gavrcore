<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">{{ title }}</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <Link href="/admin/galleries" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отмена
                </Link>
            </div>
        </div>

        <div class="p-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-2xl">
                <div class="space-y-4">
                    <!-- Название -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите название..."
                        />
                    </div>

                    <!-- Тип -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Тип галереи *</label>
                        <select v-model="form.type" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="grid">Сетка (Grid)</option>
                            <option value="slideshow">Слайд-шоу (Slideshow)</option>
                            <option value="slider">Слайдер (Slider)</option>
                            <option value="switcher">Switcher</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">Тип галереи можно будет изменить позже</p>
                    </div>

                    <!-- Статус -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
                        <div class="flex items-center gap-3">
                            <button
                                @click="form.status = !form.status"
                                type="button"
                                class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                :class="form.status ? 'bg-indigo-600' : 'bg-gray-300'"
                            >
                                <span
                                    class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform shadow-sm"
                                    :class="form.status ? 'translate-x-4.5' : 'translate-x-0.5'"
                                />
                            </button>
                            <span class="text-sm text-gray-700">{{ form.status ? 'Опубликовано' : 'Черновик' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';

const props = defineProps<{
    user: any;
    title: string;
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const form = ref({
    title: '',
    type: 'grid',
    status: true,
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const save = async () => {
    if (!form.value.title.trim()) {
        showNotification('Введите название галереи', 'error');
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post('/admin/galleries', {
            title: form.value.title,
            type: form.value.type,
            status: form.value.status,
            settings: {},
        });

        showNotification('Галерея создана', 'success');

        // Переход на страницу редактирования
        setTimeout(() => {
            router.visit(`/admin/galleries/${response.data.id}/edit`);
        }, 500);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
    } finally {
        loading.value = false;
    }
};
</script>
