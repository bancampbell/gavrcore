<template>
    <EmptyLayout :user="user">
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер пользователей: Создать пользователя</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
                </button>
                <button @click="saveAndCreate" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и создать
                </button>
                <Link href="/admin/users" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Имя *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите имя..."
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Логин *</label>
                        <input
                            v-model="form.username"
                            @input="updateUsername"
                            type="text"
                            class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите логин..."
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex gap-6 px-6 py-6 min-h-[calc(100vh-250px)]">
            <div class="flex-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="user@example.com"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Пароль *</label>
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Введите пароль..."
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-80">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Группы</h3>
                        <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto">
                            <label v-for="group in groups" :key="group.id" class="flex items-center gap-2 mb-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    :value="group.id"
                                    v-model="form.groups"
                                    class="rounded border-gray-300"
                                />
                                <span class="text-sm text-gray-700">{{ group.name }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Статус</h3>
                        <select
                            v-model="form.blocked"
                            class="w-full border rounded px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 transition"
                            :class="form.blocked ? 'bg-red-600 text-white border-red-700' : 'bg-green-600 text-white border-green-700'"
                        >
                            <option :value="false" class="bg-white text-gray-800">Активен</option>
                            <option :value="true" class="bg-white text-gray-800">Заблокирован</option>
                        </select>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Активация</h3>
                        <select
                            v-model="form.activated"
                            class="w-full border rounded px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 transition"
                            :class="form.activated ? 'bg-green-600 text-white border-green-700' : 'bg-gray-500 text-white border-gray-600'"
                        >
                            <option :value="true" class="bg-white text-gray-800">Активирован</option>
                            <option :value="false" class="bg-white text-gray-800">Не активирован</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { usersApi } from '@/api/users';

const props = defineProps<{
    user: any;
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
