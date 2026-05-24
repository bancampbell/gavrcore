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
                    <h3 class="text-xl font-bold text-gray-900">{{ modalTitle }}</h3>
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

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import ConfirmModal from '../../../components/shared/ConfirmModal.vue';
import Toast from '../../../components/shared/Toast.vue';

const props = defineProps({
    user: Object,
    categories: Object,
    parentOptions: Object,
    filters: Object
});

const filters = ref({
    search: props.filters?.search || ''
});

const selectedCategories = ref([]);
const allSelected = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });
const modalOpen = ref(false);
const editingId = ref(null);
const loading = ref(false);
const deleteModalOpen = ref(false);
const categoryToDelete = ref(null);
const deleteLoading = ref(false);

const deleteMessage = computed(() => {
    if (categoryToDelete.value) {
        return `Вы уверены, что хотите удалить категорию «${categoryToDelete.value.name}»? Все подкатегории также будут удалены.`;
    }
    if (selectedCategories.value.length > 0) {
        return `Вы уверены, что хотите удалить ${selectedCategories.value.length} выбранных категорий? Все подкатегории также будут удалены.`;
    }
    return '';
});

const form = ref({
    name: '',
    alias: '',
    description: '',
    parent_id: null
});

let notificationTimeout = null;
let searchTimeout = null;

const showNotification = (message, type = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const getIndent = (category) => {
    let indent = '';
    if (category.depth > 0) {
        indent = '— '.repeat(category.depth);
    }
    return indent;
};

const selectAll = () => {
    if (allSelected.value) {
        selectedCategories.value = props.categories.data.map(c => c.id);
    } else {
        selectedCategories.value = [];
    }
};

watch(selectedCategories, (val) => {
    allSelected.value = val.length === props.categories.data?.length;
});

const applyFilters = () => {
    router.get('/admin/categories', filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const debounceSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const resetFilters = () => {
    filters.value = { search: '' };
    applyFilters();
};

const prevPage = () => {
    if (props.categories.current_page > 1) {
        router.get(`/admin/categories?page=${props.categories.current_page - 1}`, filters.value);
    }
};

const nextPage = () => {
    if (props.categories.current_page < props.categories.last_page) {
        router.get(`/admin/categories?page=${props.categories.current_page + 1}`, filters.value);
    }
};

const openCreateModal = () => {
    editingId.value = null;
    form.value = {
        name: '',
        alias: '',
        description: '',
        parent_id: null
    };
    modalOpen.value = true;
};

const openEditModal = (category) => {
    editingId.value = category.id;
    form.value = {
        name: category.name,
        alias: category.alias || '',
        description: category.description || '',
        parent_id: category.parent_id || null
    };
    modalOpen.value = true;
};

const submitForm = async () => {
    loading.value = true;

    try {
        if (editingId.value) {
            await axios.post(`/admin/categories/${editingId.value}`, {
                _method: 'PUT',
                ...form.value
            }, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification('Категория обновлена', 'success');
        } else {
            await axios.post('/admin/categories', form.value, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification('Категория создана', 'success');
        }
        modalOpen.value = false;
        applyFilters();
    } catch (error) {
        showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
    } finally {
        loading.value = false;
    }
};

const openDeleteModal = (category) => {
    categoryToDelete.value = category;
    deleteModalOpen.value = true;
};
const openEditSelectedModal = () => {
    if (selectedCategories.value.length !== 1) return;
    const category = props.categories.data.find(c => c.id === selectedCategories.value[0]);
    if (category) {
        openEditModal(category);
    }
};

const bulkDelete = () => {
    if (selectedCategories.value.length === 0) return;
    deleteModalOpen.value = true;
};


const confirmDeleteHandler = async () => {
    deleteLoading.value = true;

    try {
        if (categoryToDelete.value) {
            await axios.delete(`/admin/categories/${categoryToDelete.value.id}`, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification('Категория удалена', 'success');
            categoryToDelete.value = null;
        } else if (selectedCategories.value.length > 0) {
            for (const id of selectedCategories.value) {
                await axios.delete(`/admin/categories/${id}`, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
                });
            }
            showNotification(`${selectedCategories.value.length} категорий удалено`, 'success');
            selectedCategories.value = [];
        }
        deleteModalOpen.value = false;
        applyFilters();
    } catch (error) {
        showNotification('Ошибка при удалении', 'error');
    } finally {
        deleteLoading.value = false;
    }
};
</script>
