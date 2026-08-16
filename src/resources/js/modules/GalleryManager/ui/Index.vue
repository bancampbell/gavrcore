<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер галерей</h1>
                <div class="flex flex-wrap gap-2.5">
                    <button @click="openCreateModal" class="admin-btn admin-btn-primary">
                        + Создать галерею
                    </button>

                    <template v-if="selectedIds.length === 1">
                        <button @click="editSelected" class="admin-btn admin-btn-secondary">
                            Редактировать
                        </button>
                    </template>

                    <template v-if="selectedIds.length > 0">
                        <button @click="publishSelected" class="admin-btn admin-btn-secondary">
                            Опубликовать
                        </button>
                        <button @click="unpublishSelected" class="admin-btn admin-btn-secondary">
                            Снять с публикации
                        </button>
                        <button @click="openDeleteModal" class="admin-btn admin-btn-danger">
                            Удалить
                        </button>
                    </template>
                </div>

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
                        <label class="admin-filter-label">Тип</label>
                        <select v-model="filters.type" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="grid">Сетка</option>
                            <option value="slideshow">Слайд-шоу</option>
                            <option value="slider">Слайдер</option>
                            <option value="switcher">Switcher</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select v-model="filters.status" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="1">Опубликовано</option>
                            <option value="0">Черновик</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <!-- Mobile view -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="gallery in paginatedGalleries" :key="gallery.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    v-model="selectedIds"
                                    :value="gallery.id"
                                    class="mt-1 admin-checkbox"
                                >
                                <div class="flex-1">
                                    <Link :href="`/admin/galleries/${gallery.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ gallery.title }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ gallery.id }}</div>
                                    <div class="text-xs text-slate-400">Тип: {{ gallery.type }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-slate-500">Изображений: {{ gallery.images_count || 0 }}</span>
                                        <span :class="gallery.status ? 'text-emerald-600' : 'text-slate-500'">
                                            {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                                        </span>
                                        <span class="text-slate-400">Дата: {{ formatDate(gallery.created_at) }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <code class="text-xs bg-slate-100 px-2 py-0.5 rounded">[gallery id="{{ gallery.id }}"]</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Desktop view -->
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" v-model="allSelected" class="admin-checkbox">
                                </th>
                                <th class="col-title">Название</th>
                                <th class="col-type">Тип</th>
                                <th class="col-images">Изображений</th>
                                <th class="col-status">Статус</th>
                                <th class="col-shortcode">Шорткод</th>
                                <th class="col-created">Дата создания</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="gallery in paginatedGalleries"
                                :key="gallery.id"
                                :class="{ 'bg-blue-50/50': selectedIds.includes(gallery.id) }"
                            >
                                <td class="col-checkbox" @click.stop="toggleSelect(gallery.id)">
                                    <input
                                        type="checkbox"
                                        :checked="selectedIds.includes(gallery.id)"
                                        class="admin-checkbox"
                                    />
                                </td>
                                <td class="col-title" @click.stop="toggleSelect(gallery.id)">
                                    <Link :href="`/admin/galleries/${gallery.id}/edit`" class="title-text">
                                        {{ gallery.title }}
                                    </Link>
                                </td>
                                <td class="col-type" @click.stop="toggleSelect(gallery.id)">
                                    <span class="gallery-type-badge">{{ gallery.type }}</span>
                                </td>
                                <td class="col-images" @click.stop="toggleSelect(gallery.id)">
                                    <span class="stat-badge stat-badge-blue">{{ gallery.images_count || 0 }}</span>
                                </td>
                                <td class="col-status" @click.stop="toggleSelect(gallery.id)">
                                        <span class="status-badge" :class="gallery.status ? 'status-published' : 'status-draft'">
                                            {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                                        </span>
                                </td>
                                <td class="col-shortcode" @click.stop="toggleSelect(gallery.id)">
                                    <code class="shortcode">[gallery id="{{ gallery.id }}"]</code>
                                </td>
                                <td class="col-created" @click.stop="toggleSelect(gallery.id)">
                                    {{ formatDate(gallery.created_at) }}
                                </td>
                                <td class="col-id" @click.stop="toggleSelect(gallery.id)">
                                    {{ gallery.id }}
                                </td>
                            </tr>
                            <tr v-if="paginatedGalleries.length === 0">
                                <td colspan="8" class="text-center py-10 text-slate-400">
                                    Нет галерей
                                    <p class="text-xs mt-1">Создайте первую галерею</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="filteredGalleries.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ pagination.from }} - {{ pagination.to }} из {{ pagination.total }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="pagination.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">{{ pagination.current_page }} / {{ pagination.last_page }}</span>
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

        <GalleryModal
            :show="createModalOpen"
            @close="closeCreateModal"
            @save="handleCreateGallery"
        />

        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление галерей"
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
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import GalleryModal from './components/GalleryModal.vue';
import { useGalleries } from './composables/useGalleries';
import { useGalleryFilters } from './composables/useGalleryFilters';
import { useGallerySelection } from './composables/useGallerySelection';
import { useGalleryNotifications } from './composables/useGalleryNotifications';
import { useGalleryModals } from './composables/useGalleryModals';
import { galleryApi } from '../infrastructure/api/gallery-api';
import type { GalleryListItem } from '../types';

const props = defineProps<{ user: any; title: string }>();

const { galleries, loading, loadGalleries } = useGalleries();
const { filters, filteredGalleries, resetFilters: resetGalleryFilters } = useGalleryFilters(galleries);
const { selectedIds, allSelected, toggleSelect, clearSelection } = useGallerySelection(filteredGalleries);
const { notification, showNotification } = useGalleryNotifications();
const { createModalOpen, deleteModalOpen, deleteLoading, openCreateModal, closeCreateModal, openDeleteModal, closeDeleteModal } = useGalleryModals();

const perPage = ref(20);
const currentPage = ref(1);
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const pagination = computed(() => {
    const total = filteredGalleries.value.length;
    const lastPage = Math.ceil(total / perPage.value) || 1;
    const from = total === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1;
    const to = Math.min(currentPage.value * perPage.value, total);

    return { current_page: currentPage.value, last_page: lastPage, from, to, total };
});

const paginatedGalleries = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredGalleries.value.slice(start, start + perPage.value);
});

const deleteMessage = computed(() => {
    const count = selectedIds.value.length;
    return count === 1
        ? 'Вы уверены, что хотите удалить выбранную галерею? Это действие нельзя отменить.'
        : `Вы уверены, что хотите удалить ${count} галерей? Это действие нельзя отменить.`;
});

const debounceSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage.value = 1;
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    currentPage.value = 1;
};

const resetFilters = () => {
    resetGalleryFilters();
    currentPage.value = 1;
};

const formatDate = (date: string | null): string => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const handleCreateGallery = async (data: { title: string; type: string; status: boolean }) => {
    try {
        const response = await galleryApi.store({
            title: data.title,
            type: data.type as GalleryListItem['type'],
            status: data.status,
            settings: {},
        });
        closeCreateModal();
        showNotification('Галерея создана', 'success');
        router.visit(`/admin/galleries/${response.data.id}/edit`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
    }
};

const editSelected = () => {
    if (selectedIds.value.length === 1) {
        router.visit(`/admin/galleries/${selectedIds.value[0]}/edit`);
    }
};

const confirmDelete = async () => {
    if (selectedIds.value.length === 0) return;
    deleteLoading.value = true;
    try {
        await Promise.all(selectedIds.value.map(id => galleryApi.destroy(id)));
        showNotification(`${selectedIds.value.length} галерей удалено`, 'success');
        clearSelection();
        closeDeleteModal();
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка при удалении', 'error');
    } finally {
        deleteLoading.value = false;
    }
};

const publishSelected = async () => {
    if (selectedIds.value.length === 0) return;
    try {
        await Promise.all(selectedIds.value.map(id => galleryApi.publish(id)));
        showNotification('Галереи опубликованы', 'success');
        clearSelection();
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка при публикации', 'error');
    }
};

const unpublishSelected = async () => {
    if (selectedIds.value.length === 0) return;
    try {
        await Promise.all(selectedIds.value.map(id => galleryApi.unpublish(id)));
        showNotification('Галереи сняты с публикации', 'success');
        clearSelection();
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка при снятии с публикации', 'error');
    }
};

const prevPage = () => {
    if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
    if (currentPage.value < pagination.value.last_page) currentPage.value++;
};

watch([() => filters.search, () => filters.type, () => filters.status], () => {
    currentPage.value = 1;
}, { deep: true });

onMounted(() => {
    loadGalleries();
});

defineExpose({
    galleries,
    loading,
    filters,
    filteredGalleries,
    paginatedGalleries,
    pagination,
    currentPage,
    selectedIds,
    allSelected,
    toggleSelect,
    clearSelection,
    resetFilters,
    createModalOpen,
    deleteModalOpen,
    deleteLoading,
    openCreateModal,
    closeCreateModal,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
    publishSelected,
    unpublishSelected,
    editSelected,
    deleteMessage,
    formatDate,
    nextPage,
    prevPage,
    notification,
    handleCreateGallery,
    loadGalleries,
});
</script>
