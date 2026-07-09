<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер групп: Создать группу</h1>
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
                        @click="saveAndCreate"
                        :disabled="loading"
                        class="admin-btn admin-btn-secondary"
                    >
                        Сохранить и создать
                    </button>
                    <button
                        @click="cancel"
                        class="admin-btn admin-btn-secondary"
                    >
                        Отменить
                    </button>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full p-6">
                    <!-- Верхняя панель: название + алиас -->
                    <div class="flex flex-wrap items-center gap-6 mb-6">
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Название *</label>
                            <input
                                v-model="form.name"
                                @input="updateAlias"
                                type="text"
                                class="admin-form-input"
                                style="width: 384px;"
                                placeholder="Введите название..."
                            />
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Алиас</label>
                            <input
                                v-model="form.alias"
                                type="text"
                                class="admin-form-input"
                                style="width: 256px;"
                                placeholder="останется пустым - сгенерируется автоматически"
                            />
                        </div>
                    </div>

                    <!-- Описание + правая панель -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Левая колонка -->
                        <div class="lg:col-span-2">
                            <div>
                                <label class="admin-form-label">Описание</label>
                                <textarea
                                    v-model="form.description"
                                    rows="5"
                                    class="admin-form-textarea w-full max-w-md"
                                    placeholder="Введите описание группы..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- Правая колонка -->
                        <div class="space-y-4">
                            <div>
                                <h3 class="admin-form-label">Порядок сортировки</h3>
                                <input
                                    v-model.number="form.ordering"
                                    type="number"
                                    class="admin-form-input w-full"
                                    placeholder="0"
                                />
                            </div>

                            <div>
                                <h3 class="admin-form-label">Статус</h3>
                                <select
                                    v-model="form.status"
                                    class="admin-form-select w-full"
                                    :class="{
                                        'admin-form-select-status-published': form.status === true,
                                        'admin-form-select-status-draft': form.status === false
                                    }"
                                >
                                    <option :value="true" class="bg-white text-slate-800">Опубликовано</option>
                                    <option :value="false" class="bg-white text-slate-800">Скрыто</option>
                                </select>
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
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { groupsApi } from '@/api/groups';

const props = defineProps<{
    user: any;
    title?: string;
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    name: '',
    alias: '',
    description: '',
    status: true,
    ordering: 0,
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const updateAlias = () => {
    if (!form.value.name) {
        form.value.alias = '';
        return;
    }
    form.value.alias = form.value.name
        .toLowerCase()
        .replace(/[^a-zа-яё0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

const cancel = () => {
    router.visit('/admin/groups');
};

const save = async () => {
    if (!form.value.name) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;
    try {
        await groupsApi.create(form.value);
        showNotification('Группа сохранена', 'success');
        form.value = { name: '', alias: '', description: '', status: true, ordering: 0 };
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.name) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;
    try {
        await groupsApi.create(form.value);
        router.visit('/admin/groups?message=Группа+создана');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

const saveAndCreate = async () => {
    if (!form.value.name) {
        showNotification('Введите название', 'error');
        return;
    }

    loading.value = true;
    try {
        await groupsApi.create(form.value);
        form.value = { name: '', alias: '', description: '', status: true, ordering: 0 };
        showNotification('Группа создана. Можете создать следующую', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};
</script>
