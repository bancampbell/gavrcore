<template>
    <AdminLayout :user="user">
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <Link href="/admin/materials/create" class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition">
                        + Создать материал
                    </Link>
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
                            <Link :href="`/admin/materials/${material.id}/edit`" class="font-medium text-[#3071a9] hover:text-[#3071a9] hover:underline cursor-pointer">
                                {{ material.title }}
                            </Link>
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

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import { useMaterials } from '../../../composables/useMaterials';
import type { User, Category, MaterialsData, MaterialFilters } from '../../../types';

const props = defineProps<{
    user: User;
    materials: MaterialsData;
    categories: Category[];
    authors: User[];
    filters?: MaterialFilters;
}>();

const {
    filters,
    selectedMaterials,
    allSelected,
    notification,
    formatDate,
    selectAll,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    moveToTrash,
    publishSelected,
    unpublishSelected
} = useMaterials(props);
</script>
