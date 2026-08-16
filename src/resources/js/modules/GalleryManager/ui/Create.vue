<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">{{ title }}</h1>
            </div>

            <div class="admin-page-content">
                <div class="admin-page-card w-full max-w-2xl mx-auto p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="admin-form-label">Название *</label>
                            <input v-model="form.title" type="text" class="admin-form-input" placeholder="Введите название..." />
                        </div>
                        <div>
                            <label class="admin-form-label">Тип галереи *</label>
                            <select v-model="form.type" class="admin-form-select">
                                <option value="grid">Сетка (Grid)</option>
                                <option value="slideshow">Слайд-шоу (Slideshow)</option>
                                <option value="slider">Слайдер (Slider)</option>
                                <option value="switcher">Switcher</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3">
                            <button @click="form.status = !form.status" type="button" class="admin-toggle" :class="form.status ? 'admin-toggle-on' : 'admin-toggle-off'">
                                <span class="admin-toggle-slider" :class="form.status ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'" />
                            </button>
                            <span class="text-sm text-slate-700">{{ form.status ? 'Опубликовано' : 'Черновик' }}</span>
                        </div>
                        <div class="flex gap-2 pt-4">
                            <button @click="save" :disabled="loading || !form.title.trim()" class="btn-primary">
                                {{ loading ? 'Сохранение...' : 'Создать' }}
                            </button>
                            <button @click="cancel" class="btn-cancel">Отмена</button>
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
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { galleryApi } from '../infrastructure/api/gallery-api';
import { useGalleryNotifications } from './composables/useGalleryNotifications';

const props = defineProps<{ user: any; title: string }>();

const { notification, showNotification } = useGalleryNotifications();

const loading = ref(false);
const form = ref({
    title: '',
    type: 'grid' as const,
    status: true,
});

const save = async () => {
    if (!form.value.title.trim()) return;
    loading.value = true;
    try {
        const response = await galleryApi.store({
            title: form.value.title,
            type: form.value.type,
            status: form.value.status,
            settings: {},
        });
        showNotification('Галерея создана', 'success');
        router.visit(`/admin/galleries/${response.data.id}/edit`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
    } finally {
        loading.value = false;
    }
};

const cancel = () => { router.visit('/admin/galleries'); };
</script>
