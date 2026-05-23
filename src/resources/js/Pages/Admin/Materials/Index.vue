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
                    <button class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">
                        Опубликовать
                    </button>
                    <button class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">
                        Снять с публикации
                    </button>
                    <button class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition">
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
                            <option value="draft">Черновик</option>
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
                    <div class="w-32">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Доступ</label>
                        <select v-model="filters.access" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option value="public">Public</option>
                            <option value="registered">Registered</option>
                            <option value="special">Special</option>
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
                        <th class="text-left px-4 py-3 font-bold text-[#3071a9]">Доступ</th>
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
                                    'bg-green-100 text-green-800': material.access === 'public',
                                    'bg-yellow-100 text-yellow-800': material.access === 'registered',
                                    'bg-red-100 text-red-800': material.access === 'special'
                                }">
                                    {{ material.access }}
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
    </AdminLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '../../../Layouts/AdminLayout.vue';

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
    access: props.filters?.access || '',
    author: props.filters?.author || ''
});

const selectedMaterials = ref([]);
const allSelected = ref(false);

let searchTimeout = null;

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
        access: '',
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
</script>
