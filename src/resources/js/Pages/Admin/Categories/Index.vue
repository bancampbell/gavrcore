<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full">
            <!-- Панель действий + фильтры (sticky) -->
            <div class="admin-page-actions flex-shrink-0">
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="openCreateModal"
                        class="admin-btn admin-btn-primary"
                    >
                        + Создать категорию
                    </button>
                    <template v-if="selectedCategories.length > 0">
                        <button
                            @click="openEditSelectedModal"
                            :disabled="selectedCategories.length !== 1"
                            class="admin-btn admin-btn-secondary"
                        >
                            Редактировать
                        </button>
                        <button
                            @click="bulkDelete"
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
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card">
                    <!-- Мобильная версия (карточки) -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="category in categories.data" :key="category.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedCategories" :value="category.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <div class="admin-category-link">
                                        {{ getIndent(category) }}{{ category.name }}
                                    </div>
                                    <div class="text-xs text-slate-400">Алиас: {{ category.alias }}</div>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ category.id }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-emerald-600">Опубликовано: {{ category.published_count || 0 }}</span>
                                        <span class="text-amber-600">Не опубликовано: {{ category.draft_count || 0 }}</span>
                                        <span class="text-rose-600">В корзине: {{ category.trash_count || 0 }}</span>
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
                                <th class="col-title">Название</th>
                                <th class="col-published">Опубликовано</th>
                                <th class="col-draft">Не опубликовано</th>
                                <th class="col-trash">В корзине</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="category in categories.data"
                                :key="category.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedCategories.includes(category.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedCategories.includes(category.id)"
                                        @change="toggleSelect(category.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название - ссылка только на текст, клик по ячейке = выбор -->
                                <td class="col-title" @click="toggleSelect(category.id)">
                                    <Link
                                        :href="`/admin/categories/${category.id}/edit`"
                                        class="category-name-link"
                                        style="display: inline-block !important;"
                                        @click.stop
                                    >
                                        {{ getIndent(category) }}{{ category.name }}
                                        <span class="category-alias">({{ category.alias }})</span>
                                    </Link>
                                </td>

                                <!-- Остальные ячейки - клик = выбор -->
                                <td class="col-published" @click="toggleSelect(category.id)">
                                    <span class="stat-badge stat-badge-green">
                                        {{ category.published_count || 0 }}
                                    </span>
                                </td>

                                <td class="col-draft" @click="toggleSelect(category.id)">
                                    <span class="stat-badge stat-badge-amber">
                                        {{ category.draft_count || 0 }}
                                    </span>
                                </td>

                                <td class="col-trash" @click="toggleSelect(category.id)">
                                    <span class="stat-badge stat-badge-rose">
                                        {{ category.trash_count || 0 }}
                                    </span>
                                </td>

                                <td class="col-id" @click="toggleSelect(category.id)">{{ category.id }}</td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="categories.data?.length === 0">
                                <td colspan="6" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет категорий
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первую категорию</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="categories.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ categories.from || 0 }} - {{ categories.to || 0 }} из {{ categories.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="categories.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ categories.current_page }} / {{ categories.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="categories.current_page === categories.last_page"
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
        <CategoryModal
            :show="modalOpen"
            :is-edit="!!editingId"
            :category-data="editingId ? categories.data.find(c => c.id === editingId) : null"
            :parent-options="parentOptions"
            :loading="loading"
            @close="modalOpen = false"
            @save="handleModalSave"
        />

        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление категории"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDeleteHandler"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import CategoryModal from './components/CategoryModal.vue';
import { categoriesApi } from '@/api/categories';
import { useCategories } from '@/composables/useCategories';
import type { CategoryFormData } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Category {
    id: number;
    name: string;
    alias: string;
    description: string | null;
    parent_id: number | null;
    depth: number;
    published_count: number;
    draft_count: number;
    trash_count: number;
}

interface CategoriesData {
    data: Category[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface ParentOptions {
    [key: number]: string;
}

interface Filters {
    search?: string;
}

const props = defineProps<{
    user: User;
    title?: string;
    categories: CategoriesData;
    parentOptions: ParentOptions;
    filters?: Filters;
}>();

const {
    filters,
    selectedCategories,
    allSelected,
    notification,
    modalOpen,
    editingId,
    loading,
    deleteModalOpen,
    deleteLoading,
    deleteMessage,
    getIndent,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    openCreateModal,
    openEditSelectedModal,
    bulkDelete,
    confirmDeleteHandler,
    showNotification
} = useCategories(props);

const handleModalSave = (formData: CategoryFormData) => {
    if (editingId.value) {
        categoriesApi.update(editingId.value, formData)
            .then(() => {
                showNotification('Категория обновлена', 'success');
                modalOpen.value = false;
                applyFilters();
            })
            .catch((error) => {
                showNotification(error.response?.data?.message || 'Ошибка при обновлении', 'error');
            });
    } else {
        categoriesApi.create(formData)
            .then(() => {
                showNotification('Категория создана', 'success');
                modalOpen.value = false;
                applyFilters();
            })
            .catch((error) => {
                showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
            });
    }
};

const toggleSelect = (id: number) => {
    const index = selectedCategories.value.indexOf(id);
    if (index === -1) {
        selectedCategories.value.push(id);
    } else {
        selectedCategories.value.splice(index, 1);
    }
};
</script>
