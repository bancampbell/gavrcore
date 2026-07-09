<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер пользователей: Создать пользователя</h1>
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
                    <!-- Верхняя панель: имя + логин -->
                    <div class="flex flex-wrap items-center gap-6 mb-6">
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Имя *</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="admin-form-input"
                                style="width: 384px;"
                                placeholder="Введите имя..."
                            />
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="admin-form-label whitespace-nowrap">Логин *</label>
                            <input
                                v-model="form.username"
                                @input="updateUsername"
                                type="text"
                                class="admin-form-input"
                                style="width: 256px;"
                                placeholder="Введите логин..."
                            />
                        </div>
                    </div>

                    <!-- Остальные поля -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Левая колонка -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="admin-form-label">E-mail *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="admin-form-input w-full max-w-md"
                                    placeholder="user@example.com"
                                />
                            </div>
                            <div>
                                <label class="admin-form-label">Пароль *</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="admin-form-input w-full max-w-md"
                                    placeholder="Введите пароль..."
                                />
                            </div>
                        </div>

                        <!-- Правая колонка -->
                        <div class="space-y-4">
                            <!-- Группы -->
                            <div>
                                <h3 class="admin-form-label">Группы</h3>
                                <div class="border border-slate-300 rounded-lg p-3 max-h-48 overflow-y-auto space-y-3">
                                    <div v-for="group in groups" :key="group.id" class="flex items-center justify-between">
                                        <span class="text-sm text-slate-700">{{ group.name }}</span>
                                        <button
                                            @click="toggleGroup(group.id)"
                                            type="button"
                                            class="admin-toggle"
                                            :class="isGroupSelected(group.id) ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        >
                                            <span
                                                class="admin-toggle-slider"
                                                :class="isGroupSelected(group.id) ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                            />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Статус -->
                            <div>
                                <h3 class="admin-form-label">Статус</h3>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm text-slate-600">Заблокирован</label>
                                    <button
                                        @click="form.blocked = !form.blocked"
                                        type="button"
                                        class="admin-toggle"
                                        :class="form.blocked ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span
                                            class="admin-toggle-slider"
                                            :class="form.blocked ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                        />
                                    </button>
                                    <span class="text-sm font-medium" :class="form.blocked ? 'text-rose-600' : 'text-emerald-600'">
                                        {{ form.blocked ? 'Заблокирован' : 'Активен' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Активация -->
                            <div>
                                <h3 class="admin-form-label">Активация</h3>
                                <div class="flex items-center gap-3">
                                    <label class="text-sm text-slate-600">Активирован</label>
                                    <button
                                        @click="form.activated = !form.activated"
                                        type="button"
                                        class="admin-toggle"
                                        :class="form.activated ? 'admin-toggle-on' : 'admin-toggle-off'"
                                    >
                                        <span
                                            class="admin-toggle-slider"
                                            :class="form.activated ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                        />
                                    </button>
                                    <span class="text-sm font-medium" :class="form.activated ? 'text-emerald-600' : 'text-slate-500'">
                                        {{ form.activated ? 'Активирован' : 'Не активирован' }}
                                    </span>
                                </div>
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
import { usersApi } from '@/api/users';

const props = defineProps<{
    user: any;
    title?: string;
    groups: any[];
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const form = ref({
    name: '',
    username: '',
    email: '',
    password: '',
    blocked: false,
    activated: true,
    groups: [] as number[],
});

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = window.setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const updateUsername = () => {
    if (!form.value.username && form.value.name) {
        form.value.username = form.value.name
            .toLowerCase()
            .replace(/[^a-zа-яё0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
};

const isGroupSelected = (groupId: number) => {
    return form.value.groups.includes(groupId);
};

const toggleGroup = (groupId: number) => {
    const index = form.value.groups.indexOf(groupId);
    if (index === -1) {
        form.value.groups.push(groupId);
    } else {
        form.value.groups.splice(index, 1);
    }
};

const cancel = () => {
    router.visit('/admin/users');
};

const save = async () => {
    if (!form.value.name) {
        showNotification('Введите имя', 'error');
        return;
    }
    if (!form.value.email) {
        showNotification('Введите email', 'error');
        return;
    }
    if (!form.value.password) {
        showNotification('Введите пароль', 'error');
        return;
    }

    loading.value = true;
    try {
        await usersApi.create(form.value);
        showNotification('Пользователь сохранён', 'success');
        form.value = {
            name: '',
            username: '',
            email: '',
            password: '',
            blocked: false,
            activated: true,
            groups: [],
        };
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const saveAndClose = async () => {
    if (!form.value.name) {
        showNotification('Введите имя', 'error');
        return;
    }
    if (!form.value.email) {
        showNotification('Введите email', 'error');
        return;
    }
    if (!form.value.password) {
        showNotification('Введите пароль', 'error');
        return;
    }

    loading.value = true;
    try {
        await usersApi.create(form.value);
        router.visit('/admin/users?message=Пользователь+создан');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

const saveAndCreate = async () => {
    if (!form.value.name) {
        showNotification('Введите имя', 'error');
        return;
    }
    if (!form.value.email) {
        showNotification('Введите email', 'error');
        return;
    }
    if (!form.value.password) {
        showNotification('Введите пароль', 'error');
        return;
    }

    loading.value = true;
    try {
        await usersApi.create(form.value);
        form.value = {
            name: '',
            username: '',
            email: '',
            password: '',
            blocked: false,
            activated: true,
            groups: [],
        };
        showNotification('Пользователь создан. Можете создать следующего', 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};
</script>
