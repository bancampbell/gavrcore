<template>
    <AdminLayout :user="user">
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

            <!-- Таблица -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 w-10">
                            <input type="checkbox" @change="selectAll" v-model="allSelected" class="rounded border-gray-300">
                        </th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">ID</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Название</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9] text-center">Опубликовано</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9] text-center">Не опубликовано</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9] text-center">🗑</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(category, index) in categories.data" :key="category.id"
                        :class="[
                                'border-b border-gray-100 hover:bg-gray-50 cursor-pointer',
                                index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                            ]">
                        <td class="px-4 py-3">
                            <input type="checkbox" v-model="selectedCategories" :value="category.id" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ category.id }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-[#3071a9] hover:underline cursor-pointer">
                                {{ getIndent(category) }}{{ category.name }}
                                <span class="text-xs text-gray-400 ml-1">(Алиас: {{ category.alias }})</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="group relative inline-block">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 text-green-600 text-xs font-medium cursor-help">
                                        {{ category.published_count || 0 }}
                                    </span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                    Опубликовано: {{ category.published_count || 0 }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="group relative inline-block">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-100 text-yellow-600 text-xs font-medium cursor-help">
                                        {{ category.draft_count || 0 }}
                                    </span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                    Не опубликовано: {{ category.draft_count || 0 }}
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="group relative inline-block">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs font-medium cursor-help">
                                        {{ category.trash_count || 0 }}
                                    </span>
                                <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                                    В корзине: {{ category.trash_count || 0 }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
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
        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">{{ editingId ? 'Редактировать категорию' : 'Создать категорию' }}</h3>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Название *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Алиас</label>
                        <input
                            v-model="form.alias"
                            type="text"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Родительская категория</label>
                        <select v-model="form.parent_id" class="w-full border border-gray-300 rounded-xl px-4 py-2">
                            <option :value="null">— Без родителя —</option>
                            <option v-for="(name, id) in parentOptions" :key="id" :value="id">
                                {{ name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Описание</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="flex-1 bg-indigo-600 text-white py-2 rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ loading ? 'Сохранение...' : (editingId ? 'Обновить' : 'Создать') }}
                        </button>
                        <button
                            type="button"
                            @click="modalOpen = false"
                            class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-xl font-medium hover:bg-gray-300"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import ConfirmModal from '../../../components/shared/ConfirmModal.vue';
import Toast from '../../../components/shared/Toast.vue';
import { useCategories } from '@/composables/useCategories';

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
    categoryToDelete,
    deleteLoading,
    form,
    deleteMessage,
    getIndent,
    selectAll,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    openCreateModal,
    openEditModal,
    openEditSelectedModal,
    submitForm,
    openDeleteModal,
    bulkDelete,
    confirmDeleteHandler
} = useCategories(props);
</script>
