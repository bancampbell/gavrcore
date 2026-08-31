<template>
    <AdminLayout :user="user">
        <Head><title>{{ title }}</title></Head>
        <div class="flex flex-col h-full">
            <div class="admin-page-actions flex-shrink-0">
                <h1 class="admin-page-title">Все меню</h1>
                <div class="flex flex-wrap gap-2.5">
                    <Link :href="selectedMenuTypeId ? `/admin/menu/types/${selectedMenuTypeId}/items/create` : (menuTypes.length > 0 ? `/admin/menu/types/${menuTypes[0].id}/items/create` : '#')" class="admin-btn admin-btn-primary no-style" :class="{ 'opacity-50 pointer-events-none': menuTypes.length === 0 }">+ Создать пункт меню</Link>
                    <template v-if="selectedItems.length > 0">
                        <button @click="openEditSelected" :disabled="selectedItems.length !== 1" class="admin-btn admin-btn-secondary">Редактировать</button>
                        <button @click="handleBulkPublish" class="admin-btn admin-btn-secondary">Опубликовать</button>
                        <button @click="handleBulkUnpublish" class="admin-btn admin-btn-secondary">Снять с публикации</button>
                        <button @click="openDeleteModalForSelected" class="admin-btn admin-btn-danger">Удалить</button>
                    </template>
                </div>
                <div class="admin-filters-inline">
                    <div class="w-48">
                        <label class="admin-filter-label">Выбрать меню</label>
                        <select v-model="selectedMenuTypeId" @change="changeMenuType" class="admin-filter-select">
                            <option :value="null">— Все меню —</option>
                            <option v-for="type in menuTypes" :key="type.id" :value="type.id">{{ type.title }}</option>
                        </select>
                    </div>
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Поиск</label>
                        <input type="text" v-model="filters.search" @input="debounceSearch" placeholder="Введите название..." class="admin-filter-input" />
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select v-model="filters.status" @change="applyFilters" class="admin-filter-select">
                            <option :value="undefined">Все</option>
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Не опубликовано</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>
            <div class="admin-page-content">
                <div class="admin-page-card">
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="item in flatItems" :key="item.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedItems" :value="item.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link :href="`/admin/menu/items/${item.id}/edit`" class="font-medium hover:underline text-[#1e5981]">{{ item.title }}</Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ item.id }}</div>
                                    <div class="text-xs text-slate-400">{{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}</div>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <span class="menu-badge">{{ item.menu_type?.title || '—' }}</span>
                                        <span class="status-badge" :class="item.status ? 'status-published' : 'status-draft'">
                                            <svg v-if="item.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                            <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                            {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox"><input type="checkbox" v-model="allSelected" class="admin-checkbox"></th>
                                <th class="col-title">Заголовок</th>
                                <th class="col-menu">Меню</th>
                                <th class="col-status">Статус</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="item in flatItems" :key="item.id" class="cursor-pointer" :class="{ 'bg-blue-50/50': selectedItems.includes(item.id) }">
                                <td class="col-checkbox"><input type="checkbox" :checked="selectedItems.includes(item.id)" @change="toggleSelect(item.id)" class="admin-checkbox" /></td>
                                <td class="col-title" @click="toggleSelect(item.id)">
                                    <Link :href="`/admin/menu/items/${item.id}/edit`" class="title-text" :style="{ display: 'inline-block !important', paddingLeft: `${item.level * 20}px` }" @click.stop>{{ item.title }}</Link>
                                    <span class="title-slug">{{ getLinkTypeLabel(item.link_type) }}: {{ getLinkValueDisplay(item.link_type, item.link_value) }}</span>
                                </td>
                                <td class="col-menu" @click="toggleSelect(item.id)"><span class="menu-badge">{{ item.menu_type?.title || '—' }}</span></td>
                                <td class="col-status" @click="toggleSelect(item.id)">
                                        <span class="status-badge" :class="item.status ? 'status-published' : 'status-draft'">
                                            <svg v-if="item.status" class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                            <svg v-else class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                                            {{ item.status ? 'Опубликовано' : 'Не опубликовано' }}
                                        </span>
                                </td>
                                <td class="col-id" @click="toggleSelect(item.id)">{{ item.id }}</td>
                            </tr>
                            <tr v-if="flatItems.length === 0">
                                <td colspan="5" style="text-align: center; padding: 40px 0; color: #94a3b8;">Нет пунктов меню<p style="font-size: 12px; margin-top: 4px;">Создайте первый пункт меню</p></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Пагинация только для режима "Все пункты" (плоский список с серверной пагинацией) -->
                    <div v-if="selectedMenuTypeId === null && flatItems.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }}</div>
                        <div class="admin-pagination-controls">
                            <button @click="prevPage" :disabled="pagination.current_page === 1" class="admin-pagination-btn">← Назад</button>
                            <span class="admin-pagination-current">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
                            <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page" class="admin-pagination-btn">Вперед →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmModal :is-open="deleteModalOpen" title="Удаление пункта меню" :message="deleteMessage" confirm-text="Удалить" type="danger" :loading="deleteLoading" @close="deleteModalOpen = false" @confirm="confirmDeleteHandler" />
        <ConfirmModal :is-open="bulkDeleteModalOpen" title="Массовое удаление" :message="bulkDeleteMessage" confirm-text="Удалить все" type="danger" :loading="bulkDeleteLoading" @close="bulkDeleteModalOpen = false" @confirm="confirmBulkDeleteHandler" />
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useMenuItems } from './composables/useMenuItems';

interface User { id: number; name: string; email: string; }
const props = defineProps<{ user: User; title?: string; menuTypeId?: number; menuTypeTitle?: string; menuItems?: any; filters?: any }>();

const {
    menuTypes, selectedMenuTypeId, flatItems, selectedItems, allSelected, pagination, filters, notification, loading,
    deleteModalOpen, deleteLoading, deleteMessage, bulkDeleteModalOpen, bulkDeleteLoading, bulkDeleteMessage,
    showNotification, toggleSelect, getLinkTypeLabel, getLinkValueDisplay, loadMenuTypes, loadData, applyFilters,
    debounceSearch, resetFilters, prevPage, nextPage, changeMenuType, openEditSelected, handleBulkPublish, handleBulkUnpublish,
    openDeleteModal, confirmDeleteHandler, openDeleteModalForSelected, confirmBulkDeleteHandler,
} = useMenuItems();

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
        await loadData();
    } else if (props.menuTypeId && menuTypes.value.length > 0) {
        selectedMenuTypeId.value = props.menuTypeId;
        pagination.value.current_page = 1;
        await loadData();
    } else {
        selectedMenuTypeId.value = null;
        await loadData();
    }
});
</script>
