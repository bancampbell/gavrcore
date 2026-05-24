<template>
    <AdminLayout :user="user">
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <button class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition">
                        + Создать материал
                    </button>
                    <button class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">
                        Изменить
                    </button>
                    <button
                        @click="publishSelected"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="unpublishSelected"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Снять с публикации
                    </button>
                    <button
                        @click="moveToTrash"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        В корзину
                    </button>
                </div>
            </div>

            <!-- Фильтры -->
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-wrap gap-4 items-end">
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
                    <div class="w-32">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Состояние</label>
                        <select v-model="filters.state" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Не опубликовано</option>
                            <option value="archived">Архив</option>
                        </select>
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Категория</label>
                        <select v-model="filters.category_id" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все категории</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Автор</label>
                        <select v-model="filters.author" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option v-for="author in authors" :key="author.id" :value="author.id">{{ author.name }}</option>
                        </select>
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
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Заголовок</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Статус</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Автор</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Язык</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Дата создания</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Просмотров</th>
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">ID</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(material, index) in materials.data" :key="material.id"
                        :class="[
                                'border-b border-gray-100 hover:bg-gray-100 cursor-pointer',
                                index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                            ]">
                        <td class="px-4 py-3">
                            <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-[#3071a9] hover:text-[#3071a9] hover:underline cursor-pointer">{{ material.title }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">Алиас: {{ material.alias }}</div>
                            <div class="text-xs text-[#3071a9] hover:underline mt-0.5 cursor-pointer">Категория: {{ material.category?.name || 'Uncategorized' }}</div>
                        </td>
                        <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium" :class="{
                                    'bg-green-100 text-green-800': material.state === 'published',
                                    'bg-red-100 text-red-800': material.state === 'draft',
                                    'bg-gray-100 text-gray-800': material.state === 'archived'
                                }">
                                    {{ material.state === 'published' ? 'Опубликовано' : material.state === 'draft' ? 'Не опубликовано' : 'Архив' }}
                                </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ material.user?.name }}</td>
                        <td class="px-4 py-3 text-gray-600">Все</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatDate(material.created_at) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ material.views }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ material.id }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ materials.from || 0 }} - {{ materials.to || 0 }} из {{ materials.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="materials.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ materials.current_page }} / {{ materials.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="materials.current_page === materials.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Toast уведомление -->
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';

const props = defineProps({
    user: Object,
    materials: Object,
    categories: Array,
    authors: Array,
    filters: Object
});

const filters = ref({
    search: props.filters?.search || '',
    state: props.filters?.state || '',
    category_id: props.filters?.category_id || '',
    author: props.filters?.author || ''
});

const selectedMaterials = ref([]);
const allSelected = ref(false);
const notification = ref({ show: false, message: '', type: 'success' });

let searchTimeout = null;
let notificationTimeout = null;

const showNotification = (message, type = 'success') => {
    if (notificationTimeout) clearTimeout(notificationTimeout);
    notification.value = { show: true, message, type };
    notificationTimeout = setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return `${d.getDate().toString().padStart(2, '0')}.${(d.getMonth() + 1).toString().padStart(2, '0')}.${d.getFullYear().toString().slice(-2)} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
};

const selectAll = () => {
    if (allSelected.value) {
        selectedMaterials.value = props.materials.data.map(m => m.id);
    } else {
        selectedMaterials.value = [];
    }
};

watch(selectedMaterials, (val) => {
    allSelected.value = val.length === props.materials.data?.length;
});

const applyFilters = () => {
    router.get('/admin/materials', filters.value, {
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
    filters.value = {
        search: '',
        state: '',
        category_id: '',
        author: ''
    };
    applyFilters();
};

const prevPage = () => {
    if (props.materials.current_page > 1) {
        router.get(`/admin/materials?page=${props.materials.current_page - 1}`, filters.value, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const nextPage = () => {
    if (props.materials.current_page < props.materials.last_page) {
        router.get(`/admin/materials?page=${props.materials.current_page + 1}`, filters.value, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

const moveToTrash = async () => {
    if (selectedMaterials.value.length === 0) return;

    try {
        const response = await axios.post('/admin/materials/bulk-trash', {
            ids: selectedMaterials.value
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        showNotification(response.data.message, 'success');
        selectedMaterials.value = [];
        applyFilters();
    } catch (error) {
        showNotification('Ошибка при перемещении в корзину', 'error');
    }
};

const publishSelected = async () => {
    if (selectedMaterials.value.length === 0) return;

    try {
        const response = await axios.post('/admin/materials/bulk-publish', {
            ids: selectedMaterials.value
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        showNotification(response.data.message, 'success');
        selectedMaterials.value = [];
        applyFilters();
    } catch (error) {
        showNotification('Ошибка при публикации', 'error');
    }
};

const unpublishSelected = async () => {
    if (selectedMaterials.value.length === 0) return;

    try {
        const response = await axios.post('/admin/materials/bulk-unpublish', {
            ids: selectedMaterials.value
        }, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
        });

        showNotification(response.data.message, 'success');
        selectedMaterials.value = [];
        applyFilters();
    } catch (error) {
        showNotification('Ошибка при снятии с публикации', 'error');
    }
};
</script>
