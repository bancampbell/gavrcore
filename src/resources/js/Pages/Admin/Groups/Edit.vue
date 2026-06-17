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

        <div class="px-6 py-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-6">
                    <!-- Описание -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full max-w-2xl border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Введите описание группы..."
                        ></textarea>
                    </div>

                    <!-- Настройки -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Порядок сортировки</label>
                            <input
                                v-model.number="form.ordering"
                                type="number"
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                            <div class="flex items-center gap-3 pt-1">
                                <button
                                    @click="form.status = !form.status"
                                    type="button"
                                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                    :class="form.status ? 'bg-green-600' : 'bg-gray-400'"
                                >
                                    <span
                                        class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                                        :class="form.status ? 'translate-x-6' : 'translate-x-1'"
                                    />
                                </button>
                                <span class="text-sm font-medium" :class="form.status ? 'text-green-600' : 'text-gray-500'">
                                    {{ form.status ? 'Опубликовано' : 'Скрыто' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Права доступа -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Права доступа</label>
                        <div class="border border-gray-300 rounded-lg p-4">
                            <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="mb-4 last:mb-0">
                                <div class="font-semibold text-gray-700 text-xs uppercase mb-2">{{ groupName }}</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    <div v-for="perm in perms" :key="perm.id" class="flex items-center gap-2">
                                        <button
                                            @click="togglePermission(perm.id)"
                                            type="button"
                                            class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                            :class="isPermissionSelected(perm.id) ? 'bg-indigo-600' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                                :class="isPermissionSelected(perm.id) ? 'translate-x-4.5' : 'translate-x-0.5'"
                                            />
                                        </button>
                                        <span class="text-sm text-gray-700">{{ perm.name }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Отдельная строка для MENU -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="font-semibold text-gray-700 text-xs uppercase mb-2">МЕНЮ</div>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="toggleMenuPermission"
                                        type="button"
                                        class="relative inline-flex items-center h-5 rounded-full w-9 transition-colors focus:outline-none flex-shrink-0"
                                        :class="isMenuSelected ? 'bg-indigo-600' : 'bg-gray-300'"
                                    >
                                        <span
                                            class="inline-block w-3.5 h-3.5 transform bg-white rounded-full transition-transform"
                                            :class="isMenuSelected ? 'translate-x-4.5' : 'translate-x-0.5'"
                                        />
                                    </button>
                                    <span class="text-sm text-gray-700">Управление меню</span>
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
        // Пропускаем menu.manage, добавим отдельно
        if (perm.key === 'menu.manage') continue;
        const group = perm.group || 'Другие';
        if (!grouped[group]) grouped[group] = [];
        grouped[group].push(perm);
    }
    return grouped;
});

const isMenuSelected = computed(() => {
    return form.value.permissions.includes(getMenuPermissionId());
});

const getMenuPermissionId = () => {
    const menuPerm = props.permissions.find((p: any) => p.key === 'menu.manage');
    return menuPerm ? menuPerm.id : null;
};

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

const isPermissionSelected = (permId: number) => {
    return form.value.permissions.includes(permId);
};

const togglePermission = (permId: number) => {
    const index = form.value.permissions.indexOf(permId);
    if (index === -1) {
        form.value.permissions.push(permId);
    } else {
        form.value.permissions.splice(index, 1);
    }
};

const toggleMenuPermission = () => {
    const menuId = getMenuPermissionId();
    if (!menuId) return;
    togglePermission(menuId);
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
