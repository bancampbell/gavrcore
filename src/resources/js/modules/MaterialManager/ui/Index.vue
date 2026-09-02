<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Менеджер материалов</h1>

                <div class="flex flex-wrap items-center gap-2.5">
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
                        <select v-model="filters.state" @change="applyFilters(1)" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Не опубликовано</option>
                            <option value="archived">Архив</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Категория</label>
                        <select v-model="filters.category_id" @change="applyFilters(1)" class="admin-filter-select">
                            <option :value="null">Все категории</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>
                    <div class="w-36">
                        <label class="admin-filter-label">Автор</label>
                        <select v-model="filters.author" @change="applyFilters(1)" class="admin-filter-select">
                            <option :value="null">Все</option>
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

            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <div class="hidden lg:block admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox">
                                    <input type="checkbox" :checked="allSelected" @change="selectAll" class="admin-checkbox">
                                </th>
                                <th class="col-title sortable" @click="toggleSort('title')">
                                    Заголовок
                                    <span v-if="filters.sort === 'title'" class="sort-arrow">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="col-status sortable" @click="toggleSort('state')">
                                    Статус
                                    <span v-if="filters.sort === 'state'" class="sort-arrow">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="col-author">Автор</th>
                                <th class="col-created sortable" @click="toggleSort('created_at')">
                                    Дата создания
                                    <span v-if="filters.sort === 'created_at'" class="sort-arrow">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th class="col-views sortable" @click="toggleSort('views')">
                                    Просмотров
                                    <span v-if="filters.sort === 'views'" class="sort-arrow">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                                <th v-if="!isLanding" class="col-home">На главной</th>
                                <th class="col-id sortable" @click="toggleSort('id')">
                                    ID
                                    <span v-if="filters.sort === 'id'" class="sort-arrow">{{ filters.direction === 'asc' ? '↑' : '↓' }}</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="material in items"
                                :key="material.id"
                                :class="{ 'bg-blue-50/50': selectedMaterials.includes(material.id) }"
                            >
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedMaterials.includes(material.id)"
                                        @change="toggleSelect(material.id)"
                                        class="admin-checkbox"
                                    />
                                </td>
                                <td class="col-title" @click="toggleSelect(material.id)">
                                    <Link :href="`/admin/materials/${material.id}/edit`" class="title-text" @click.stop>
                                        {{ material.title }}
                                    </Link>
                                    <span class="title-slug">Слаг: {{ material.slug || '—' }}</span>
                                    <span class="title-category">Категория: {{ material.category?.name || 'Без категории' }}</span>
                                </td>
                                <td class="col-status" @click="toggleSelect(material.id)">
                                    <span class="status-badge" :class="`status-${material.status_color}`">
                                        {{ material.status_label }}
                                    </span>
                                </td>
                                <td class="col-author" @click="toggleSelect(material.id)">{{ material.user?.name || '—' }}</td>
                                <td class="col-created" @click="toggleSelect(material.id)">{{ formatDate(material.created_at) }}</td>
                                <td class="col-views" @click="toggleSelect(material.id)">{{ material.views }}</td>
                                <td v-if="!isLanding" class="col-home" @click.stop>
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
                            <tr v-if="items.length === 0">
                                <td :colspan="!isLanding ? 8 : 7" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Материалов не найдено
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="material in items" :key="material.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" :checked="selectedMaterials.includes(material.id)" @change="toggleSelect(material.id)" class="mt-1 admin-checkbox">
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
                                        <span :class="`text-${material.status_color}-600`">
                                            {{ material.status_label }}
                                        </span>
                                        <span v-if="!isLanding" :class="material.show_on_homepage ? 'text-[#3071a9] font-medium' : 'text-slate-400'">
                                            {{ material.show_on_homepage ? 'На главной' : '' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ materials.from || 0 }} - {{ materials.to || 0 }} из {{ materials.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button @click="prevPage" :disabled="materials.current_page === 1" class="admin-pagination-btn">
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">{{ materials.current_page }} / {{ materials.last_page }}</span>
                            <button @click="nextPage" :disabled="materials.current_page === materials.last_page" class="admin-pagination-btn">
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
import { Head, Link, usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Toast from '@/components/shared/Toast.vue';
import { useMaterials } from './composables/useMaterials';
import type { MaterialsData } from './types';
import type { User } from '@/types';
import type { Category } from '@/modules/CategoryManager/ui/types';

const props = defineProps<{
    user: User;
    title?: string;
    materials: MaterialsData;
    categories: Category[];
    authors: User[];
    filters?: any;
    perPage?: number;
    isLanding?: boolean;
}>();

const page = usePage();

const {
    filters,
    perPage,
    selectedMaterials,
    allSelected,
    notification,
    formatDate,
    toggleSelect,
    selectAll,
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
    toggleHomepage,
    toggleSort,
    items,
} = useMaterials(props);

onMounted(() => {
    const flash = page.props.flash as { success?: string; error?: string } | undefined;
    if (flash?.success) {
        showNotification(flash.success, 'success');
    }
    if (flash?.error) {
        showNotification(flash.error, 'error');
    }
});

watch(() => page.props.flash, (flash) => {
    const f = flash as { success?: string; error?: string } | undefined;
    if (f?.success) {
        showNotification(f.success, 'success');
    }
    if (f?.error) {
        showNotification(f.error, 'error');
    }
}, { deep: true });
</script>

<style scoped>
.sortable {
    cursor: pointer;
    user-select: none;
}
.sortable:hover {
    background-color: #f1f5f9;
}
.sort-arrow {
    margin-left: 4px;
    font-size: 0.75rem;
    color: #64748b;
}
</style>
