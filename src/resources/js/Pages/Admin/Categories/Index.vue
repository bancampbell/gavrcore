<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="openCreateModal"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать категорию
                    </button>
                    <button
                        @click="openEditSelectedModal"
                        :disabled="selectedCategories.length !== 1"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Редактировать выбранную
                    </button>
                    <button
                        @click="bulkDelete"
                        :disabled="selectedCategories.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Удалить выбранные
                    </button>
                </div>

                <!-- Фильтры -->
                <div class="mt-3 flex flex-wrap gap-4 items-end">
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
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Список карточек (мобильная версия) -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="category in categories.data" :key="category.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedCategories" :value="category.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <div class="font-medium text-[#3071a9]">
                                {{ getIndent(category) }}{{ category.name }}
                            </div>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ category.id }}</div>
                            <div class="text-xs text-gray-400">Алиас: {{ category.alias }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span class="text-green-600">Опубликовано: {{ category.published_count || 0 }}</span>
                                <span class="text-yellow-600">Не опубликовано: {{ category.draft_count || 0 }}</span>
                                <span class="text-red-600">В корзине: {{ category.trash_count || 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Десктопная версия (Flexbox таблица) -->
            <div class="hidden lg:block overflow-x-auto">
                <!-- Заголовки -->
                <div class="flex bg-gray-50 border-b border-gray-200 px-4 py-3 text-sm font-medium text-gray-500">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div class="w-16 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Название</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Опубликовано</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Не опубликовано</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">В корзине</div>
                </div>

                <!-- Строки -->
                <div v-for="(category, index) in categories.data" :key="category.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">

                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedCategories" :value="category.id" class="rounded border-gray-300">
                    </div>

                    <div class="w-16 flex items-center justify-center text-gray-600">{{ category.id }}</div>

                    <div class="flex-1 flex items-start">
                        <div class="font-medium text-[#3071a9]">
                            {{ getIndent(category) }}{{ category.name }}
                            <span class="text-xs text-gray-400 ml-1">(Алиас: {{ category.alias }})</span>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <div class="group relative inline-block">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 text-xs font-medium cursor-help">
                                {{ category.published_count || 0 }}
                            </span>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                Опубликовано: {{ category.published_count || 0 }}
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <div class="group relative inline-block">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 text-yellow-600 text-xs font-medium cursor-help">
                                {{ category.draft_count || 0 }}
                            </span>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                Не опубликовано: {{ category.draft_count || 0 }}
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <div class="group relative inline-block">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs font-medium cursor-help">
                                {{ category.trash_count || 0 }}
                            </span>
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                В корзине: {{ category.trash_count || 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ categories.from || 0 }} - {{ categories.to || 0 }} из {{ categories.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="categories.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ categories.current_page }} / {{ categories.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="categories.current_page === categories.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования -->
        <CategoryModal
            :show="modalOpen"
            :is-edit="!!editingId"
            :category-data="editingId ? categories.data.find(c => c.id === editingId) : null"
            :parent-options="parentOptions"
            :loading="loading"
            @close="modalOpen = false"
            @save="handleModalSave"
        />

        <!-- Модальное окно подтверждения удаления -->
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

        <!-- Toast уведомление -->
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
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
        // Обновление
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
        // Создание
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
</script>
