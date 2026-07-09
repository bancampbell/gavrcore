<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер групп: Редактировать группу</h1>
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

                    <!-- Права доступа -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="admin-form-label mb-4">Права доступа</h3>
                        <div class="admin-form-group">
                            <div v-for="(perms, groupName) in groupedPermissions" :key="groupName" class="mb-4 last:mb-0">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                    {{ groupName }}
                                </div>
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
                            <div v-if="menuPermissionId" class="mt-4 pt-4 border-t border-gray-200">
                                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                    МЕНЮ
                                </div>
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
                        <p class="text-xs text-gray-400 mt-2">Выберите действия, которые может выполнять группа</p>
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

const menuPermissionId = computed(() => {
    const menuPerm = props.permissions.find((p: any) => p.key === 'menu.manage');
    return menuPerm ? menuPerm.id : null;
});

const isMenuSelected = computed(() => {
    return menuPermissionId.value ? form.value.permissions.includes(menuPermissionId.value) : false;
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
    if (!menuPermissionId.value) return;
    togglePermission(menuPermissionId.value);
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
