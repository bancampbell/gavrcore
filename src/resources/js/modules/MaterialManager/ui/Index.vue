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
                                    <div class="title-with-tooltip">
                                        <Link :href="`/admin/materials/${material.id}/edit`" class="title-text" @click.stop>
                                            {{ material.title }}
                                        </Link>
                                        <span class="title-tooltip" :class="material.state === 'published' ? 'title-tooltip-published' : 'title-tooltip-draft'" @click.stop>
                                            <span class="title-tooltip-row">
                                                <span class="title-tooltip-label">Слаг:</span>
                                                <span class="title-tooltip-value">{{ material.slug || '—' }}</span>
                                                <button
                                                    v-if="material.slug"
                                                    @click.stop="copySlug(material.slug, material.id)"
                                                    class="title-tooltip-copy"
                                                    :title="copiedId === material.id ? 'Скопировано!' : 'Скопировать слаг'"
                                                >
                                                    <svg v-if="copiedId !== material.id" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                                        <path d="M8 3a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-2 0V4h-5V3z" />
                                                        <path d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2H4zm0 2h8v8H4V7z" />
                                                    </svg>
                                                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                                <span v-if="copiedId === material.id" class="title-tooltip-copied">Скопировано!</span>
                                            </span>
                                            <span class="title-tooltip-row">
                                                <span class="title-tooltip-label">Категория:</span>
                                                <span class="title-tooltip-value">{{ material.category?.name || 'Без категории' }}</span>
                                            </span>
                                        </span>
                                    </div>
                                </td>
                                <td class="col-status" @click="toggleSelect(material.id)">
                                    <span class="status-badge" :class="material.state === 'published' ? 'status-published' : 'status-draft'">
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
import { onMounted, ref, watch } from 'vue';
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

const copiedId = ref<number | null>(null);
let copyTimer: ReturnType<typeof setTimeout> | null = null;

async function copySlug(slug: string, id: number) {
    try {
        await navigator.clipboard.writeText(slug);
    } catch {
        // Fallback для старых браузеров / не-HTTPS
        const textarea = document.createElement('textarea');
        textarea.value = slug;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    copiedId.value = id;
    if (copyTimer) clearTimeout(copyTimer);
    copyTimer = setTimeout(() => {
        copiedId.value = null;
    }, 1500);
}

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

.title-with-tooltip {
    position: relative;
    display: inline-block;
}

.title-tooltip {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 30;
    margin-top: 6px;
    padding: 8px 12px;
    background: #ffffff;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    font-size: 0.75rem;
    line-height: 1.5;
    white-space: nowrap;
    display: flex;
    flex-direction: column;
    gap: 2px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-4px);
    transition: opacity 0.15s ease, transform 0.15s ease, visibility 0.15s;
    user-select: text;
    cursor: default;
}

/* Мостик над тултипом — заполняет зазор, чтобы курсор не «проваливался» */
.title-tooltip::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 0;
    right: 0;
    height: 8px;
}

.title-tooltip::after {
    content: '';
    position: absolute;
    top: -4px;
    left: 12px;
    border: 4px solid transparent;
    border-bottom-color: #e8dcc0;
    border-top: none;
}

/* Тултип внутри ссылки — убираем синий цвет и подчёркивание у текста */
.title-tooltip-row {
    color: #6b5d3f;
    text-decoration: none;
}

.title-tooltip-label {
    font-weight: 700;
    color: #1a1a1a;
}

.title-tooltip-value {
    font-weight: 400;
    color: #000000;
    margin-left: 6px;
}

.title-tooltip-copy {
    display: inline-flex;
    align-items: center;
    margin-left: 6px;
    padding: 2px;
    background: transparent;
    border: none;
    border-radius: 4px;
    color: #b3a17c;
    cursor: pointer;
    vertical-align: middle;
    transition: color 0.15s, background-color 0.15s;
}

.title-tooltip-copy:hover {
    color: #6b5d3f;
    background-color: rgba(107, 93, 63, 0.1);
}

.title-tooltip-copied {
    margin-left: 6px;
    color: #4ade80;
    font-size: 0.7rem;
}

/* ----- Тема: опубликовано (зелёная) ----- */
.title-tooltip-published {
    border-color: #6ee7b7 !important;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.title-tooltip-published::after {
    border-bottom-color: #6ee7b7 !important;
}

.title-tooltip-published .title-tooltip-value {
    color: #065f46;
}

.title-tooltip-published .title-tooltip-copy {
    color: #6ee7b7;
}

.title-tooltip-published .title-tooltip-copy:hover {
    color: #065f46;
    background-color: rgba(16, 185, 129, 0.1);
}

.title-tooltip-published .title-tooltip-copied {
    color: #059669;
}

/* ----- Тема: не опубликовано (красная) ----- */
.title-tooltip-draft {
    border-color: #fca5a5 !important;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
}

.title-tooltip-draft::after {
    border-bottom-color: #fca5a5 !important;
}

.title-tooltip-draft .title-tooltip-value {
    color: #991b1b;
}

.title-tooltip-draft .title-tooltip-copy {
    color: #fca5a5;
}

.title-tooltip-draft .title-tooltip-copy:hover {
    color: #991b1b;
    background-color: rgba(239, 68, 68, 0.1);
}

.title-tooltip-draft .title-tooltip-copied {
    color: #e11d48;
}

/* При наведении на заголовок ИЛИ на сам тултип — показываем */
.title-with-tooltip:hover .title-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
    pointer-events: auto;
}

.title-with-tooltip .title-tooltip {
    transition-delay: 0s, 0s, 0.4s;
}

.title-with-tooltip:hover .title-tooltip {
    transition-delay: 0s, 0s, 0s;
}
</style>
