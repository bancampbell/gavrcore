<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="selectedMenuTypeId ? `/admin/menu/types/${selectedMenuTypeId}/items/create` : (menuTypes.length > 0 ? `/admin/menu/types/${menuTypes[0].id}/items/create` : '#')"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition disabled:opacity-50"
                        :class="{ 'opacity-50 pointer-events-none': menuTypes.length === 0 }"
                    >
                        + Создать пункт меню
                    </Link>
                    <button
                        @click="openEditSelected"
                        :disabled="selectedItems.length !== 1"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Редактировать выбранный
                    </button>
                    <button
                        @click="openBulkPublishModal"
                        :disabled="selectedItems.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="openBulkUnpublishModal"
                        :disabled="selectedItems.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-red-500 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50"
                    >
                        Снять с публикации
                    </button>
                    <button
                        @click="openDeleteModalForSelected"
                        :disabled="selectedItems.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50"
                    >
                        Удалить выбранные
                    </button>
                </div>

                <!-- Фильтры и выбор меню -->
                <div class="mt-3 flex flex-wrap gap-4 items-end">
                    <div class="min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Выбрать меню</label>
                        <select
                            v-model="selectedMenuTypeId"
                            @change="changeMenuType"
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm"
                        >
                            <option :value="null">— Выбор меню (все пункты) —</option>
                            <option v-for="type in menuTypes" :key="type.id" :value="type.id">
                                {{ type.title }}
                            </option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Поиск</label>
                        <input
                            type="text"
                            v-model="filters.search"
                            @input="debounceSearch"
                            placeholder="Введите название..."
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Не опубликовано</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Grid таблица -->
            <div class="hidden lg:block overflow-x-auto">
                <!-- Заголовки -->
                <div class="grid bg-gray-50 border-b border-gray-200 px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider"
                     style="grid-template-columns: 40px 60px 1fr 1.5fr 140px">
                    <div class="flex items-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div>ID</div>
                    <div>Заголовок</div>
                    <div>Меню</div>
                    <div class="text-center">Статус</div>
                </div>

                <!-- Строки -->
                <div v-for="(item, index) in flatItems" :key="item.id"
                     class="grid px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                     style="grid-template-columns: 40px 60px 1fr 1.5fr 140px">

                    <div class="flex items-center">
                        <input type="checkbox" v-model="selectedItems" :value="item.id" class="rounded border-gray-300">
                    </div>

                    <div class="flex items-center text-gray-600">{{ item.id }}</div>

                    <div class="flex items-start" :style="{ paddingLeft: `${item.level * 20}px` }">
                        <div>
                            <button
                                @click="openEditModal(item.id)"
                                class="font-medium text-[#3071a9] hover:underline text-left"
                            >
                                {{ item.title }}
                            </button>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <span :class="getMenuColorClass(item.menu_type?.title)">
                            {{ item.menu_type?.title || '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-center">
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-full font-medium',
                                item.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'
                            ]"
                        >
                            {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Мобильная версия - карточки -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="item in flatItems" :key="item.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedItems" :value="item.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <button @click="openEditModal(item.id)" class="font-medium text-[#3071a9] hover:underline">
                                {{ item.title }}
                            </button>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ item.id }}</div>
                            <div class="text-xs text-gray-400">
                                {{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}
                            </div>
                            <div class="mt-2">
                                <span class="text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-600">
                                    {{ item.menu_type?.title || '—' }}
                                </span>
                                <span
                                    :class="[
                                        'ml-2 px-2 py-1 text-xs rounded-full font-medium',
                                        item.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'
                                    ]"
                                >
                                    {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ pagination.current_page }} / {{ pagination.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно для смены статуса (одиночное) -->
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

        <!-- Модальное окно для массовой публикации -->
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

        <!-- Модальное окно для массового снятия с публикации -->
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

        <!-- Модальное окно подтверждения удаления (одиночное) -->
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

        <!-- Модальное окно подтверждения массового удаления -->
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

        <!-- Toast уведомление -->
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

const getMenuColorClass = (menuTitle?: string) => {
    const colors: Record<string, string> = {
        'Main Menu': 'px-2 py-1 text-xs rounded-full font-medium bg-blue-100 text-blue-700',
        'Menu login': 'px-2 py-1 text-xs rounded-full font-medium bg-purple-100 text-purple-700',
        'Nevidimia': 'px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700',
    };

    if (menuTitle && colors[menuTitle]) {
        return colors[menuTitle];
    }

    return 'px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-600';
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
