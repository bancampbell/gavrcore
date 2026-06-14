<template>
    <EmptyLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white border-b border-gray-200">
            <div class="px-6 py-4">
                <h1 class="text-xl font-semibold text-gray-800">Менеджер групп: Редактировать группу</h1>
            </div>
            <div class="px-6 pb-4 flex gap-2">
                <button @click="save" :disabled="loading" class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition disabled:opacity-50">
                    Сохранить
                </button>
                <button @click="saveAndClose" :disabled="loading" class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition disabled:opacity-50">
                    Сохранить и закрыть
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

                    <div>
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Права доступа</h3>
                        <div class="border border-gray-300 rounded-lg p-3 max-h-64 overflow-y-auto">
                            <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="mb-3">
                                <div class="font-semibold text-gray-700 text-xs uppercase mb-2">{{ groupName || 'Другие' }}</div>
                                <div class="space-y-1">
                                    <label v-for="perm in perms" :key="perm.id" class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input
                                            type="checkbox"
                                            :value="perm.id"
                                            v-model="form.permissions"
                                            class="rounded border-gray-300"
                                        />
                                        <span>{{ perm.name }}</span>
                                        <span class="text-xs text-gray-400 font-mono ml-1">({{ perm.key }})</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Выберите действия, которые может выполнять группа</p>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </EmptyLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, router } from '@inertiajs/vue3';
import EmptyLayout from '@/layouts/EmptyLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { groupsApi } from '@/api/groups';

const props = defineProps<{
    user: any;
    title?: string;
    editGroup: any;
    permissions: any[];
    groupPermissions: number[];
}>();

const loading = ref(false);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
let notificationTimeout: number | null = null;

const groupedPermissions = computed(() => {
    const grouped: Record<string, any[]> = {};
    for (const perm of props.permissions || []) {
        const group = perm.group || 'Другие';
        if (!grouped[group]) grouped[group] = [];
        grouped[group].push(perm);
    }
    return grouped;
});

const form = ref({
    name: '',
    alias: '',
    description: '',
    status: true,
    ordering: 0,
    permissions: [] as number[],
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
        await groupsApi.update(props.editGroup.id, form.value);
        showNotification('Группа сохранена', 'success');
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
        await groupsApi.update(props.editGroup.id, form.value);
        router.visit('/admin/groups?message=Группа+обновлена');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        loading.value = false;
    }
};

onMounted(() => {
    form.value.name = props.editGroup.name;
    form.value.alias = props.editGroup.alias || '';
    form.value.description = props.editGroup.description || '';
    form.value.status = props.editGroup.status;
    form.value.ordering = props.editGroup.ordering || 0;
    form.value.permissions = props.groupPermissions || [];
});
</script>
