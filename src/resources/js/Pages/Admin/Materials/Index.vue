<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий + фильтры -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <div class="flex flex-wrap gap-2.5">
                    <Link href="/admin/materials/create" class="admin-btn admin-btn-primary no-style">
                        + Создать материал
                    </Link>
                    <template v-if="selectedMaterials.length > 0">
                        <button
                            @click="editSelected"
                            :disabled="selectedMaterials.length !== 1"
                            class="admin-btn admin-btn-secondary"
                        >
                            Изменить
                        </button>
                        <button
                            @click="publishSelected"
                            class="admin-btn admin-btn-secondary"
                        >
                            Опубликовать
                        </button>
                        <button
                            @click="unpublishSelected"
                            class="admin-btn admin-btn-secondary"
                        >
                            Снять с публикации
                        </button>
                        <button
                            @click="moveToTrash"
                            class="admin-btn admin-btn-danger"
                        >
                            В корзину
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
                    <div class="w-32">
                        <label class="admin-filter-label">Состояние</label>
                        <select v-model="filters.state" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Не опубликовано</option>
                            <option value="archived">Архив</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Категория</label>
                        <select v-model="filters.category_id" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все категории</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="admin-filter-label">Автор</label>
                        <select v-model="filters.author" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option v-for="author in authors" :key="author.id" :value="author.id">{{ author.name }}</option>
                        </select>
                    </div>
                    <div class="w-20">
                        <label class="admin-filter-label">Показывать</label>
                        <select
                            v-model="perPage"
                            @change="changePerPage"
                            class="admin-filter-select"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <!-- Мобильная версия (карточки) -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="material in materials.data" :key="material.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedMaterials" :value="material.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link :href="`/admin/materials/${material.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ material.title }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ material.id }}</div>
                                    <div class="text-xs text-slate-400">Слаг: {{ material.slug || '—' }}</div>
                                    <div class="text-xs text-[#3071a9]">Категория: {{ material.category?.name || 'Без категории' }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-slate-500">Автор: {{ material.user?.name || '—' }}</span>
                                        <span class="text-slate-500">Просмотров: {{ material.views }}</span>
                                        <span class="text-slate-500">Дата: {{ formatDate(material.created_at) }}</span>
                                        <span :class="{
                                            'text-emerald-600': material.state === 'published',
                                            'text-rose-600': material.state === 'draft',
                                            'text-slate-500': material.state === 'archived'
                                        }">
                                            {{ material.state === 'published' ? 'Опубликовано' : material.state === 'draft' ? 'Не опубликовано' : 'Архив' }}
                                        </span>
                                        <span :class="material.show_on_homepage ? 'text-[#3071a9] font-medium' : 'text-slate-400'">
                                            {{ material.show_on_homepage ? 'На главной' : '' }}
                                        </span>
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
                                <th class="col-title">Заголовок</th>
                                <th class="col-status">Статус</th>
                                <th class="col-author">Автор</th>
                                <th class="col-created">Дата создания</th>
                                <th class="col-views">Просмотров</th>
                                <th class="col-home">На главной</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="material in materials.data"
                                :key="material.id"
                                :class="{ 'bg-blue-50/50': selectedMaterials.includes(material.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedMaterials.includes(material.id)"
                                        @change="toggleSelect(material.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название - ссылка только на текст, клик по ячейке = выбор -->
                                <td class="col-title" @click="toggleSelect(material.id)">
                                    <Link :href="`/admin/materials/${material.id}/edit`" class="title-text" style="display: inline-block !important;" @click.stop>
                                        {{ material.title }}
                                    </Link>
                                    <span class="title-slug">Слаг: {{ material.slug || '—' }}</span>
                                    <span class="title-category">Категория: {{ material.category?.name || 'Без категории' }}</span>
                                </td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(material.id)">
                                    <span class="status-badge" :class="{
                                        'status-published': material.state === 'published',
                                        'status-draft': material.state === 'draft',
                                        'status-archived': material.state === 'archived'
                                    }">
                                        <svg v-if="material.state === 'published'" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else-if="material.state === 'draft'" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <svg v-else width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="8" stroke-width="2.5" />
                                        </svg>
                                        {{ material.state === 'published' ? 'Опубликовано' : material.state === 'draft' ? 'Не опубликовано' : 'Архив' }}
                                    </span>
                                </td>

                                <td class="col-author" @click="toggleSelect(material.id)" :title="material.user?.name">
                                    {{ material.user?.name || '—' }}
                                </td>

                                <td class="col-created" @click="toggleSelect(material.id)">
                                    {{ formatDate(material.created_at) }}
                                </td>

                                <td class="col-views" @click="toggleSelect(material.id)">{{ material.views }}</td>

                                <td class="col-home" @click.stop>
                                    <div
                                        class="admin-toggle"
                                        :class="material.show_on_homepage ? 'admin-toggle-on' : 'admin-toggle-off'"
                                        @click="toggleHomepage(material)"
                                    >
                                        <span
                                            class="admin-toggle-slider"
                                            :class="material.show_on_homepage ? 'admin-toggle-slider-on' : 'admin-toggle-slider-off'"
                                        ></span>
                                    </div>
                                </td>

                                <td class="col-id" @click="toggleSelect(material.id)">{{ material.id }}</td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="materials.data.length === 0">
                                <td colspan="8" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Материалов не найдено
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ materials.from || 0 }} - {{ materials.to || 0 }} из {{ materials.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="materials.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ materials.current_page }} / {{ materials.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="materials.current_page === materials.last_page"
                                class="admin-pagination-btn"
                            >
                                Вперед →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
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
    perPage?: number;
}>();

const urlParams = new URLSearchParams(window.location.search);
const message = urlParams.get('message');

const {
    filters,
    perPage,
    selectedMaterials,
    allSelected,
    notification,
    formatDate,
    applyFilters,
    debounceSearch,
    resetFilters,
    changePerPage,
    prevPage,
    nextPage,
    moveToTrash,
    publishSelected,
    unpublishSelected,
    editSelected,
    showNotification,
    toggleHomepage
} = useMaterials(props);

const toggleSelect = (id: number) => {
    const index = selectedMaterials.value.indexOf(id);
    if (index === -1) {
        selectedMaterials.value.push(id);
    } else {
        selectedMaterials.value.splice(index, 1);
    }
};

onMounted(() => {
    if (message) {
        showNotification(decodeURIComponent(message), 'success');
        const url = new URL(window.location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, '', url.toString());
    }
});
</script>
