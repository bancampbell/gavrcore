<template>
    <EmptyLayout :user="user">
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер групп: Создать группу</h1>
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
                <Link href="/admin/groups" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
                    Отменить
                </Link>
            </div>
        </div>

        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Название *</label>
                        <input
                            v-model="form.name"
                            @input="updateAlias"
                            type="text"
                            class="w-96 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите название..."
                        />
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Алиас</label>
                        <input
                            v-model="form.alias"
                            type="text"
                            class="w-64 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="останется пустым - сгенерируется автоматически"
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                            <textarea
                                v-model="form.description"
                                rows="5"
                                class="w-full max-w-md border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                placeholder="Введите описание группы..."
                            ></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-80">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Порядок сортировки</h3>
                        <input
                            v-model.number="form.ordering"
                            type="number"
                            class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm"
                        />
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Статус</h3>
                        <select
                            v-model="form.status"
                            class="w-full border rounded px-3 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 transition"
                            :class="form.status ? 'bg-green-600 text-white border-green-700' : 'bg-red-600 text-white border-red-700'"
                        >
                            <option :value="true" class="bg-white text-gray-800">Опубликовано</option>
                            <option :value="false" class="bg-white text-gray-800">Скрыто</option>
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
import { groupsApi } from '@/api/groups';

const props = defineProps<{
    user: any;
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
