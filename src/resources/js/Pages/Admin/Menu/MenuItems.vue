<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full">
            <!-- Панель действий + фильтры (sticky) -->
            <div class="admin-page-actions flex-shrink-0">
                <div class="flex flex-wrap gap-2.5">
                    <Link
                        :href="selectedMenuTypeId ? `/admin/menu/types/${selectedMenuTypeId}/items/create` : (menuTypes.length > 0 ? `/admin/menu/types/${menuTypes[0].id}/items/create` : '#')"
                        class="admin-btn admin-btn-primary no-style"
                        :class="{ 'opacity-50 pointer-events-none': menuTypes.length === 0 }"
                    >
                        + Создать пункт меню
                    </Link>
                    <template v-if="selectedItems.length > 0">
                        <button
                            @click="openEditSelected"
                            :disabled="selectedItems.length !== 1"
                            class="admin-btn admin-btn-secondary"
                        >
                            Редактировать
                        </button>
                        <button
                            @click="openBulkPublishModal"
                            class="admin-btn admin-btn-secondary"
                        >
                            Опубликовать
                        </button>
                        <button
                            @click="openBulkUnpublishModal"
                            class="admin-btn admin-btn-secondary"
                        >
                            Снять с публикации
                        </button>
                        <button
                            @click="openDeleteModalForSelected"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить
                        </button>
                    </template>
                </div>

                <!-- Фильтры -->
                <div class="admin-filters-inline">
                    <div class="w-48">
                        <label class="admin-filter-label">Выбрать меню</label>
                        <select
                            v-model="selectedMenuTypeId"
                            @change="changeMenuType"
                            class="admin-filter-select"
                        >
                            <option :value="null">— Все меню —</option>
                            <option v-for="type in menuTypes" :key="type.id" :value="type.id">
                                {{ type.title }}
                            </option>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Поиск</label>
                        <input
                            type="text"
                            v-model="filters.search"
                            @input="debounceSearch"
                            placeholder="Введите название..."
                            class="admin-filter-input"
                        />
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="admin-filter-select"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Не опубликовано</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card">
                    <!-- Мобильная версия (карточки) -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="item in flatItems" :key="item.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedItems" :value="item.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link
                                        :href="`/admin/menu/items/${item.id}/edit`"
                                        class="font-medium hover:underline text-[#1e5981]"
                                    >
                                        {{ item.title }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ item.id }}</div>
                                    <div class="text-xs text-slate-400">
                                        {{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span class="menu-badge">
                                            {{ item.menu_type?.title || '—' }}
                                        </span>
                                        <span
                                            class="status-badge"
                                            :class="item.status ? 'status-published' : 'status-draft'"
                                        >
                                            <svg v-if="item.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Десктопная версия -->
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" v-model="allSelected" class="admin-checkbox">
                                </th>
                                <th class="col-title">Заголовок</th>
                                <th class="col-menu">Меню</th>
                                <th class="col-status">Статус</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="item in flatItems"
                                :key="item.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedItems.includes(item.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedItems.includes(item.id)"
                                        @change="toggleSelect(item.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(item.id)">
                                    <Link
                                        :href="`/admin/menu/items/${item.id}/edit`"
                                        class="title-text"
                                        :style="{
                                            display: 'inline-block !important',
                                            paddingLeft: `${item.level * 20}px`
                                        }"
                                        @click.stop
                                    >
                                        {{ item.title }}
                                    </Link>
                                    <span class="title-slug">
                                        {{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}
                                    </span>
                                </td>

                                <!-- Меню -->
                                <td class="col-menu" @click="toggleSelect(item.id)">
                                    <span class="menu-badge">
                                        {{ item.menu_type?.title || '—' }}
                                    </span>
                                </td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(item.id)">
                                    <span
                                        class="status-badge"
                                        :class="item.status ? 'status-published' : 'status-draft'"
                                    >
                                        <svg v-if="item.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                                    </span>
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(item.id)">
                                    {{ item.id }}
                                </td>
                            </tr>

                            <tr v-if="flatItems.length === 0">
                                <td colspan="5" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет пунктов меню
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первый пункт меню</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="flatItems.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="pagination.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ pagination.current_page }} / {{ pagination.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="pagination.current_page === pagination.last_page"
                                class="admin-pagination-btn"
                            >
                                Вперед →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалки -->
        <ConfirmModal
            :is-open="toggleStatusModalOpen"
            title="Изменение статуса"
            :message="toggleStatusMessage"
            confirm-text="Подтвердить"
            type="warning"
            :loading="loading"
            @close="toggleStatusModalOpen = false"
            @confirm="confirmToggleStatus"
        />

        <ConfirmModal
            :is-open="bulkPublishModalOpen"
            title="Массовая публикация"
            :message="bulkPublishMessage"
            confirm-text="Опубликовать"
            type="warning"
            :loading="loading"
            @close="bulkPublishModalOpen = false"
            @confirm="confirmBulkPublish"
        />

        <ConfirmModal
            :is-open="bulkUnpublishModalOpen"
            title="Массовое снятие с публикации"
            :message="bulkUnpublishMessage"
            confirm-text="Снять с публикации"
            type="danger"
            :loading="loading"
            @close="bulkUnpublishModalOpen = false"
            @confirm="confirmBulkUnpublish"
        />

        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление пункта меню"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDeleteHandler"
        />

        <ConfirmModal
            :is-open="bulkDeleteModalOpen"
            title="Массовое удаление"
            :message="bulkDeleteMessage"
            confirm-text="Удалить все"
            type="danger"
            :loading="bulkDeleteLoading"
            @close="bulkDeleteModalOpen = false"
            @confirm="confirmBulkDeleteHandler"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { menuTypesApi, menuItemsApi, type MenuType, type MenuItem } from '@/api/menu';

interface User {
    id: number;
    name: string;
    email: string;
}

interface ApiResponse {
    data: MenuItem[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    user: User;
    title?: string;
    menuTypeId?: number;
    menuTypeTitle?: string;
    menuItems?: {
        data: MenuItem[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters?: {
        search?: string;
        status?: boolean;
    };
}>();

const menuTypes = ref<MenuType[]>([]);
const selectedMenuTypeId = ref<number | null>(null);
const flatItems = ref<(MenuItem & { level: number; menu_type?: { title: string } })[]>([]);
const selectedItems = ref<number[]>([]);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0
});

const allSelected = computed({
    get: () => {
        if (!flatItems.value.length) return false;
        return selectedItems.value.length === flatItems.value.length;
    },
    set: (val: boolean) => {
        if (val) {
            selectedItems.value = flatItems.value.map(i => i.id);
        } else {
            selectedItems.value = [];
        }
    },
});

const filters = ref({
    search: props.filters?.search || '',
    status: props.filters?.status as boolean | undefined,
});

const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
const loading = ref(false);
const deleteModalOpen = ref(false);
const deleteLoading = ref(false);
const itemToDelete = ref<MenuItem | null>(null);
const deleteMessage = ref('');

const bulkDeleteModalOpen = ref(false);
const bulkDeleteLoading = ref(false);
const bulkDeleteMessage = ref('');

const toggleStatusModalOpen = ref(false);
const toggleStatusMessage = ref('');
const toggleStatusData = ref<{ id: number; status: boolean } | null>(null);

const bulkPublishModalOpen = ref(false);
const bulkPublishMessage = ref('');

const bulkUnpublishModalOpen = ref(false);
const bulkUnpublishMessage = ref('');

let searchTimeout: any = null;

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const toggleSelect = (id: number) => {
    const index = selectedItems.value.indexOf(id);
    if (index === -1) {
        selectedItems.value.push(id);
    } else {
        selectedItems.value.splice(index, 1);
    }
};

const getLinkTypeLabel = (type: string) => {
    const labels: Record<string, string> = {
        url: 'URL',
        material: 'Материал',
        separator: 'Разделитель',
        heading: 'Заголовок',
        external: 'Внешний URL'
    };
    return labels[type] || type;
};

const getLinkValueDisplay = (type: string, value: string | null) => {
    if (!value) return '—';
    if (type === 'material') return `ID: ${value}`;
    return value;
};

const flattenTree = (items: MenuItem[], level = 0): (MenuItem & { level: number })[] => {
    let result: (MenuItem & { level: number })[] = [];
    for (const item of items) {
        result.push({ ...item, level });
        if (item.children?.length) {
            result = [...result, ...flattenTree(item.children, level + 1)];
        }
    }
    return result;
};

const loadAllItems = async () => {
    try {
        const params: any = {
            page: pagination.value.current_page
        };
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status !== undefined) params.status = filters.value.status;

        const response = await axios.get<ApiResponse>('/admin/menu/items/all-data', { params });
        const data = response.data;

        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            from: data.from || 0,
            to: data.to || 0,
            total: data.total
        };

        flatItems.value = data.data.map((item: MenuItem) => {
            const menuType = menuTypes.value.find(t => t.id === item.menu_type_id);
            return {
                ...item,
                level: 0,
                menu_type: { title: menuType?.title || '—' }
            };
        });
    } catch (error) {
        console.error('Error loading all items:', error);
        flatItems.value = [];
        pagination.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
    }
};

const loadMenuItemsForType = async () => {
    if (!selectedMenuTypeId.value) return;

    try {
        const params: any = {
            page: pagination.value.current_page,
            per_page: 20
        };
        if (filters.value.search) params.search = filters.value.search;
        if (filters.value.status !== undefined) params.status = filters.value.status;

        const response = await menuItemsApi.getAll(selectedMenuTypeId.value, params);
        const data = response.data;

        pagination.value = {
            current_page: (data as any).current_page || 1,
            last_page: (data as any).last_page || 1,
            from: (data as any).from || 0,
            to: (data as any).to || 0,
            total: (data as any).total || 0
        };

        const items = (data as any).data || [];

        const buildTree = (items: MenuItem[], parentId: number | null = null, level = 0): (MenuItem & { level: number })[] => {
            const result: (MenuItem & { level: number })[] = [];
            const children = items.filter(item => item.parent_id === parentId);

            for (const child of children) {
                result.push({ ...child, level });
                const grandchildren = buildTree(items, child.id, level + 1);
                result.push(...grandchildren);
            }
            return result;
        };

        const treeItems = buildTree(items);
        const currentMenuType = menuTypes.value.find(t => t.id === selectedMenuTypeId.value);

        flatItems.value = treeItems.map(item => ({
            ...item,
            menu_type: { title: currentMenuType?.title || '' }
        }));
    } catch (error) {
        console.error('Error loading menu items for type:', error);
        flatItems.value = [];
    }
};

const loadMenuTypes = async () => {
    const response = await menuTypesApi.getAll();
    menuTypes.value = response.data.data;
};

const loadData = async () => {
    if (selectedMenuTypeId.value === null) {
        await loadAllItems();
    } else {
        await loadMenuItemsForType();
    }
};

const applyFilters = () => {
    if (selectedMenuTypeId.value === null) {
        pagination.value.current_page = 1;
        loadAllItems();
    } else {
        pagination.value.current_page = 1;
        loadMenuItemsForType();
    }
};

const debounceSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const resetFilters = () => {
    filters.value = { search: '', status: undefined };
    applyFilters();
};

const prevPage = () => {
    if (selectedMenuTypeId.value === null && pagination.value.current_page > 1) {
        pagination.value.current_page--;
        loadAllItems();
    } else if (selectedMenuTypeId.value !== null && pagination.value.current_page > 1) {
        pagination.value.current_page--;
        loadMenuItemsForType();
    }
};

const nextPage = () => {
    if (selectedMenuTypeId.value === null && pagination.value.current_page < pagination.value.last_page) {
        pagination.value.current_page++;
        loadAllItems();
    } else if (selectedMenuTypeId.value !== null && pagination.value.current_page < pagination.value.last_page) {
        pagination.value.current_page++;
        loadMenuItemsForType();
    }
};

const changeMenuType = () => {
    if (selectedMenuTypeId.value) {
        router.visit(`/admin/menu/types/${selectedMenuTypeId.value}/items`);
    } else {
        router.visit('/admin/menu/items/all');
    }
};

const openEditModal = (id: number) => {
    router.visit(`/admin/menu/items/${id}/edit`);
};

const openEditSelected = () => {
    if (selectedItems.value.length === 1) {
        router.visit(`/admin/menu/items/${selectedItems.value[0]}/edit`);
    }
};

const updateLocalStatus = (ids: number[], status: boolean) => {
    flatItems.value.forEach(item => {
        if (ids.includes(item.id)) {
            item.status = status;
        }
    });
};

const openToggleStatusModal = (id: number, status: boolean, title?: string) => {
    toggleStatusData.value = { id, status };
    toggleStatusMessage.value = `Вы уверены, что хотите ${status ? 'опубликовать' : 'снять с публикации'} пункт "${title || 'меню'}"?`;
    toggleStatusModalOpen.value = true;
};

const confirmToggleStatus = async () => {
    if (!toggleStatusData.value) return;
    loading.value = true;
    try {
        await menuItemsApi.updateStatus(toggleStatusData.value.id, toggleStatusData.value.status);
        updateLocalStatus([toggleStatusData.value.id], toggleStatusData.value.status);
        showNotification(`Пункт меню ${toggleStatusData.value.status ? 'опубликован' : 'скрыт'}`, 'success');
        toggleStatusModalOpen.value = false;
        toggleStatusData.value = null;
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    } finally {
        loading.value = false;
    }
};

const openBulkPublishModal = () => {
    if (selectedItems.value.length === 0) return;

    if (selectedItems.value.length === 1) {
        const item = flatItems.value.find(i => i.id === selectedItems.value[0]);
        if (item) {
            openToggleStatusModal(item.id, true, item.title);
        }
    } else {
        bulkPublishMessage.value = `Вы уверены, что хотите опубликовать ${selectedItems.value.length} пункт(ов) меню?`;
        bulkPublishModalOpen.value = true;
    }
};

const confirmBulkPublish = async () => {
    loading.value = true;
    try {
        for (const id of selectedItems.value) {
            await menuItemsApi.updateStatus(id, true);
        }
        updateLocalStatus(selectedItems.value, true);
        showNotification(`${selectedItems.value.length} пункт(ов) меню опубликовано`, 'success');
        selectedItems.value = [];
        bulkPublishModalOpen.value = false;
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    } finally {
        loading.value = false;
    }
};

const openBulkUnpublishModal = () => {
    if (selectedItems.value.length === 0) return;

    if (selectedItems.value.length === 1) {
        const item = flatItems.value.find(i => i.id === selectedItems.value[0]);
        if (item) {
            openToggleStatusModal(item.id, false, item.title);
        }
    } else {
        bulkUnpublishMessage.value = `Вы уверены, что хотите снять с публикации ${selectedItems.value.length} пункт(ов) меню?`;
        bulkUnpublishModalOpen.value = true;
    }
};

const confirmBulkUnpublish = async () => {
    loading.value = true;
    try {
        for (const id of selectedItems.value) {
            await menuItemsApi.updateStatus(id, false);
        }
        updateLocalStatus(selectedItems.value, false);
        showNotification(`${selectedItems.value.length} пункт(ов) меню снято с публикации`, 'success');
        selectedItems.value = [];
        bulkUnpublishModalOpen.value = false;
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    } finally {
        loading.value = false;
    }
};

const openDeleteModal = (item: MenuItem) => {
    itemToDelete.value = item;
    deleteMessage.value = `Вы уверены, что хотите удалить пункт "${item.title}"? Все дочерние пункты также будут удалены.`;
    deleteModalOpen.value = true;
};

const confirmDeleteHandler = async () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    try {
        await menuItemsApi.delete(itemToDelete.value.id);
        showNotification('Пункт меню удален', 'success');
        deleteModalOpen.value = false;
        await loadData();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
    } finally {
        deleteLoading.value = false;
        itemToDelete.value = null;
    }
};

const openDeleteModalForSelected = () => {
    if (selectedItems.value.length === 0) return;

    if (selectedItems.value.length === 1) {
        const item = flatItems.value.find(i => i.id === selectedItems.value[0]);
        if (item) {
            openDeleteModal(item);
        }
    } else {
        bulkDeleteMessage.value = `Вы уверены, что хотите удалить ${selectedItems.value.length} пункт(ов) меню? Все дочерние пункты также будут удалены.`;
        bulkDeleteModalOpen.value = true;
    }
};

const confirmBulkDeleteHandler = async () => {
    if (selectedItems.value.length === 0) return;
    bulkDeleteLoading.value = true;
    try {
        for (const id of selectedItems.value) {
            await menuItemsApi.delete(id);
        }
        showNotification(`${selectedItems.value.length} пункт(ов) меню удалено`, 'success');
        selectedItems.value = [];
        bulkDeleteModalOpen.value = false;
        await loadData();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
    } finally {
        bulkDeleteLoading.value = false;
    }
};

onMounted(async () => {
    await loadMenuTypes();

    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showNotification(decodeURIComponent(message), 'success');
        const url = new URL(window.location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, '', url.toString());
    }

    const isAllMode = window.location.pathname === '/admin/menu/items/all';

    if (isAllMode) {
        selectedMenuTypeId.value = null;
        await loadAllItems();
    } else if (props.menuTypeId && menuTypes.value.length > 0) {
        selectedMenuTypeId.value = props.menuTypeId;
        pagination.value.current_page = 1;
        await loadMenuItemsForType();
    } else {
        selectedMenuTypeId.value = null;
        await loadAllItems();
    }
});
</script>
