<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full">
            <!-- Панель действий + фильтры (sticky) -->
            <div class="admin-page-actions flex-shrink-0">
                <h1 class="admin-page-title">Менеджер меню</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="openCreateModal"
                        class="admin-btn admin-btn-primary"
                    >
                        + Создать тип меню
                    </button>
                    <template v-if="selectedMenuTypes.length > 0">
                        <button
                            @click="openEditSelectedModal"
                            :disabled="selectedMenuTypes.length !== 1"
                            class="admin-btn admin-btn-secondary"
                        >
                            Редактировать
                        </button>
                        <button
                            @click="handleBulkPublish"
                            class="admin-btn admin-btn-secondary"
                        >
                            Опубликовать
                        </button>
                        <button
                            @click="handleBulkUnpublish"
                            class="admin-btn admin-btn-secondary"
                        >
                            Снять с публикации
                        </button>
                        <button
                            @click="openDeleteModal"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить
                        </button>
                    </template>
                </div>

                <!-- Фильтры -->
                <div class="admin-filters-inline">
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
                        <div v-for="type in menuTypes.data" :key="type.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedMenuTypes" :value="type.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <button
                                        @click="goToMenuItems(type.id, type.title)"
                                        class="menu-type-name"
                                    >
                                        {{ type.title }}
                                    </button>
                                    <div class="text-sm text-slate-500 mt-1">Алиас: {{ type.alias }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-slate-500">ID: {{ type.id }}</span>
                                        <span class="text-blue-600">Пунктов: {{ type.items_count || 0 }}</span>
                                        <span
                                            class="status-badge"
                                            :class="type.status ? 'status-published' : 'status-draft'"
                                        >
                                            <svg v-if="type.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            {{ type.status ? 'Опубликовано' : 'Не опубликовано' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Десктопная версия - ЕДИНАЯ ТАБЛИЦА -->
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" v-model="allSelected" class="admin-checkbox">
                                </th>
                                <th class="col-id">ID</th>
                                <th class="col-title">Название</th>
                                <th class="col-alias">Алиас</th>
                                <th class="col-status">Статус</th>
                                <th class="col-items">Пунктов</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="type in menuTypes.data"
                                :key="type.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedMenuTypes.includes(type.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedMenuTypes.includes(type.id)"
                                        @change="toggleSelect(type.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(type.id)">{{ type.id }}</td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(type.id)">
                                    <button
                                        @click="goToMenuItems(type.id, type.title)"
                                        class="menu-type-name"
                                        style="display: inline-block !important; background: none; border: none; cursor: pointer; font-weight: 500; color: #1e5981; font-size: 15px; padding: 0; text-align: left;"
                                    >
                                        {{ type.title }}
                                    </button>
                                </td>

                                <!-- Алиас -->
                                <td class="col-alias" @click="toggleSelect(type.id)">{{ type.alias }}</td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(type.id)">
                                    <span
                                        class="status-badge"
                                        :class="type.status ? 'status-published' : 'status-draft'"
                                    >
                                        <svg v-if="type.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {{ type.status ? 'Опубликовано' : 'Не опубликовано' }}
                                    </span>
                                </td>

                                <td class="col-items" @click="toggleSelect(type.id)">
                                    <span class="stat-badge stat-badge-blue">
                                        {{ type.items_count || 0 }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="menuTypes.data?.length === 0">
                                <td colspan="6" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет типов меню
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первый тип меню</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="menuTypes.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ menuTypes.from || 0 }} - {{ menuTypes.to || 0 }} из {{ menuTypes.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="menuTypes.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ menuTypes.current_page }} / {{ menuTypes.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="menuTypes.current_page === menuTypes.last_page"
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
        <MenuTypeModal
            :show="modalOpen"
            :is-edit="!!editingId"
            :menu-type-data="editingMenuType"
            :loading="loading"
            @close="closeMenuTypeModal"
            @save="handleMenuTypeSave"
        />

        <!-- Модалка подтверждения удаления -->
        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление типа меню"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="closeDeleteModal"
            @confirm="confirmDelete"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useMenuTypes } from '@/composables/useMenuTypes';
import { router } from '@inertiajs/vue3';
import MenuTypeModal from './components/MenuTypeModal.vue';
import { menuTypesApi } from '@/api/menu';

interface User {
    id: number;
    name: string;
    email: string;
}

interface MenuType {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    ordering: number;
    status: boolean;
    items_count?: number;
}

interface MenuTypesData {
    data: MenuType[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Filters {
    search?: string;
    status?: boolean;
}

const props = defineProps<{
    user: User;
    title?: string;
    menuTypes: MenuTypesData;
    filters?: Filters;
}>();

// Состояние модалки удаления
const deleteModalOpen = ref(false);
const deleteLoading = ref(false);
const deleteMessage = ref('');
const itemsToDelete = ref<number[]>([]);

const goToMenuItems = (menuTypeId: number, title: string) => {
    router.visit(`/admin/menu/types/${menuTypeId}/items`, {
        props: { menuTypeId, menuTypeTitle: title }
    });
};

const toggleSelect = (id: number) => {
    const index = selectedMenuTypes.value.indexOf(id);
    if (index === -1) {
        selectedMenuTypes.value.push(id);
    } else {
        selectedMenuTypes.value.splice(index, 1);
    }
};

const {
    filters,
    selectedMenuTypes,
    allSelected,
    notification,
    modalOpen,
    editingId,
    editingMenuType,
    loading,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    openCreateModal,
    openEditModal,
    openEditSelectedModal,
    submitForm,
    showNotification,
} = useMenuTypes(props);

// Открытие модалки удаления
const openDeleteModal = () => {
    if (selectedMenuTypes.value.length === 0) return;

    const count = selectedMenuTypes.value.length;
    deleteMessage.value = count === 1
        ? 'Вы уверены, что хотите удалить выбранный тип меню? Это действие нельзя отменить.'
        : `Вы уверены, что хотите удалить ${count} типов меню? Это действие нельзя отменить.`;

    itemsToDelete.value = [...selectedMenuTypes.value];
    deleteModalOpen.value = true;
};

// Закрытие модалки удаления
const closeDeleteModal = () => {
    deleteModalOpen.value = false;
    deleteLoading.value = false;
    itemsToDelete.value = [];
};

// Подтверждение удаления
const confirmDelete = async () => {
    if (itemsToDelete.value.length === 0) return;

    deleteLoading.value = true;
    try {
        const ids = itemsToDelete.value;
        for (const id of ids) {
            await menuTypesApi.delete(id);
        }
        showNotification(`${ids.length} тип(ов) меню удалено`, 'success');
        selectedMenuTypes.value = [];
        closeDeleteModal();
        await applyFilters();
    } catch (error: any) {
        console.error('Delete error:', error);
        showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        deleteLoading.value = false;
    }
};

// Публикация
const handleBulkPublish = async () => {
    if (selectedMenuTypes.value.length === 0) return;

    loading.value = true;
    try {
        const ids = selectedMenuTypes.value;
        for (const id of ids) {
            const type = props.menuTypes.data.find(t => t.id === id);
            if (type) {
                await menuTypesApi.update(id, {
                    title: type.title,
                    alias: type.alias,
                    description: type.description,
                    ordering: type.ordering,
                    status: true
                });
            }
        }
        props.menuTypes.data.forEach(item => {
            if (ids.includes(item.id)) {
                item.status = true;
            }
        });
        showNotification(`${ids.length} тип(ов) меню опубликовано`, 'success');
        selectedMenuTypes.value = [];
        await applyFilters();
    } catch (error: any) {
        console.error('Publish error:', error);
        showNotification(error.response?.data?.message || 'Ошибка публикации', 'error');
    } finally {
        loading.value = false;
    }
};

// Снятие с публикации
const handleBulkUnpublish = async () => {
    if (selectedMenuTypes.value.length === 0) return;

    loading.value = true;
    try {
        const ids = selectedMenuTypes.value;
        for (const id of ids) {
            const type = props.menuTypes.data.find(t => t.id === id);
            if (type) {
                await menuTypesApi.update(id, {
                    title: type.title,
                    alias: type.alias,
                    description: type.description,
                    ordering: type.ordering,
                    status: false
                });
            }
        }
        props.menuTypes.data.forEach(item => {
            if (ids.includes(item.id)) {
                item.status = false;
            }
        });
        showNotification(`${ids.length} тип(ов) меню снято с публикации`, 'success');
        selectedMenuTypes.value = [];
        await applyFilters();
    } catch (error: any) {
        console.error('Unpublish error:', error);
        showNotification(error.response?.data?.message || 'Ошибка снятия с публикации', 'error');
    } finally {
        loading.value = false;
    }
};

const closeMenuTypeModal = () => {
    modalOpen.value = false;
};

const handleMenuTypeSave = async (data: any) => {
    try {
        if (editingId.value) {
            await menuTypesApi.update(editingId.value, data);
            showNotification('Тип меню обновлён', 'success');
        } else {
            await menuTypesApi.create(data);
            showNotification('Тип меню создан', 'success');
        }
        closeMenuTypeModal();
        applyFilters();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    }
};

// Импортируем ref
import { ref } from 'vue';
</script>
