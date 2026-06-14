<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <Link href="/admin/materials/create" class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition">
                        + Создать материал
                    </Link>
                    <button
                        @click="editSelected"
                        :disabled="selectedMaterials.length !== 1"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Изменить
                    </button>
                    <button
                        @click="publishSelected"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="unpublishSelected"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-red-500 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50"
                    >
                        Снять с публикации
                    </button>
                    <button
                        @click="moveToTrash"
                        :disabled="selectedMaterials.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50"
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

            <!-- Список карточек (мобильная версия) -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="material in materials.data" :key="material.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <Link :href="`/admin/materials/${material.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                {{ material.title }}
                            </Link>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ material.id }}</div>
                            <div class="text-xs text-gray-400">Слаг: {{ material.slug || '—' }}</div>
                            <div class="text-xs text-[#3071a9]">Категория: {{ material.category?.name || 'Без категории' }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span class="text-gray-500">Автор: {{ material.user?.name || '—' }}</span>
                                <span class="text-gray-500">Язык: Все</span>
                                <span class="text-gray-500">Просмотров: {{ material.views }}</span>
                                <span class="text-gray-500">Дата: {{ formatDate(material.created_at) }}</span>
                                <span :class="{
                                    'text-green-600': material.state === 'published',
                                    'text-red-600': material.state === 'draft',
                                    'text-gray-500': material.state === 'archived'
                                }">
                                    {{ material.state === 'published' ? 'Опубликовано' : material.state === 'draft' ? 'Не опубликовано' : 'Архив' }}
                                </span>
                                <span :class="material.show_on_homepage ? 'text-indigo-600 font-medium' : 'text-gray-400'">
                                    {{ material.show_on_homepage ? 'На главной' : '' }}
                                </span>
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
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Заголовок</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Статус</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Автор</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Язык</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Дата создания</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Просмотров</div>
                    <div class="w-24 flex items-center justify-center font-bold text-[#3071a9]">На главной</div>
                    <div class="w-16 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                </div>

                <!-- Строки -->
                <div v-for="(material, index) in materials.data" :key="material.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">

                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="rounded border-gray-300">
                    </div>

                    <div class="flex-1 flex items-start">
                        <div>
                            <Link :href="`/admin/materials/${material.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                {{ material.title }}
                            </Link>
                            <div class="text-xs text-gray-400 mt-0.5">Слаг: {{ material.slug || '—' }}</div>
                            <div class="text-xs text-[#3071a9] mt-0.5">Категория: {{ material.category?.name || 'Без категории' }}</div>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap" :class="{
                            'bg-green-100 text-green-800': material.state === 'published',
                            'bg-red-100 text-red-700': material.state === 'draft',
                            'bg-gray-100 text-gray-600': material.state === 'archived'
                        }">
                            {{ material.state === 'published' ? 'Опубликовано' : material.state === 'draft' ? 'Не опубликовано' : 'Архив' }}
                        </span>
                    </div>

                    <div class="flex-1 flex items-center justify-center text-gray-600 truncate" :title="material.user?.name">
                        {{ material.user?.name || '—' }}
                    </div>

                    <div class="flex-1 flex items-center justify-center text-gray-600">Все</div>

                    <div class="flex-1 flex items-center justify-center text-gray-600 whitespace-nowrap">
                        {{ formatDate(material.created_at) }}
                    </div>

                    <div class="flex-1 flex items-center justify-center text-gray-600">{{ material.views }}</div>

                    <div class="w-24 flex items-center justify-center">
                        <div class="relative inline-block w-10 h-5 rounded-full cursor-pointer transition-colors"
                             :class="material.show_on_homepage ? 'bg-indigo-600' : 'bg-gray-300'"
                             @click="toggleHomepage(material)">
                            <span class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-transform"
                                  :class="material.show_on_homepage ? 'translate-x-5' : 'translate-x-0'"></span>
                        </div>
                    </div>

                    <div class="w-16 flex items-center justify-center text-gray-600">{{ material.id }}</div>
                </div>
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

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import { useMaterials } from '../../../composables/useMaterials';
import type { User, Category, MaterialsData, MaterialFilters } from '../../../types';

const props = defineProps<{
    user: User;
    title?: string;
    materials: MaterialsData;
    categories: Category[];
    authors: User[];
    filters?: MaterialFilters;
}>();

const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');

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
    unpublishSelected,
    editSelected,
    showNotification,
    toggleHomepage
} = useMaterials(props);

onMounted(() => {
    if (message) {
        showNotification(decodeURIComponent(message), 'success');
        const url = new URL(window.location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, '', url.toString());
    }
});
</script>
