<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
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
                        <div class="border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto space-y-3">
                            <div v-for="group in groups" :key="group.id" class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">{{ group.name }}</span>
                                <button
                                    @click="toggleGroup(group.id)"
                                    type="button"
                                    class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                    :class="isGroupSelected(group.id) ? 'bg-indigo-600' : 'bg-gray-300'"
                                >
                                    <span
                                        class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                        :class="isGroupSelected(group.id) ? 'translate-x-4.5' : 'translate-x-0.5'"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Статус</h3>
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-gray-600">Заблокирован</label>
                            <button
                                @click="form.blocked = !form.blocked"
                                type="button"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                :class="form.blocked ? 'bg-red-600' : 'bg-green-600'"
                            >
                                <span
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                    :class="form.blocked ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                            <span class="text-sm font-medium" :class="form.blocked ? 'text-red-600' : 'text-green-600'">
                                {{ form.blocked ? 'Заблокирован' : 'Активен' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Активация</h3>
                        <div class="flex items-center gap-3">
                            <label class="text-sm text-gray-600">Активирован</label>
                            <button
                                @click="form.activated = !form.activated"
                                type="button"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                :class="form.activated ? 'bg-green-600' : 'bg-gray-400'"
                            >
                                <span
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                    :class="form.activated ? 'translate-x-6' : 'translate-x-1'"
                                />
                            </button>
                            <span class="text-sm font-medium" :class="form.activated ? 'text-green-600' : 'text-gray-500'">
                                {{ form.activated ? 'Активирован' : 'Не активирован' }}
                            </span>
                        </div>
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
import EmptyLayout from '@/layouts/EmptyLayout.vue';
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
