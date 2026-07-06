<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий + фильтры -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <div class="flex flex-wrap gap-2.5">
                    <button
                        @click="openCreateModal"
                        class="admin-btn admin-btn-primary"
                    >
                        + Создать галерею
                    </button>
                    <template v-if="selectedGalleries.length > 0">
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
                            @click="deleteSelected"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить
                        </button>
                    </template>
                </div>

                <!-- Фильтры -->
                <div class="admin-filters-inline">
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Поиск</label>
                        <input
                            type="text"
                            v-model="search"
                            @input="debounceSearch"
                            placeholder="Введите название..."
                            class="admin-filter-input"
                        />
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Тип</label>
                        <select v-model="filterType" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="grid">Сетка</option>
                            <option value="slideshow">Слайд-шоу</option>
                            <option value="slider">Слайдер</option>
                            <option value="switcher">Switcher</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select v-model="filterStatus" @change="applyFilters" class="admin-filter-select">
                            <option value="">Все</option>
                            <option value="1">Опубликовано</option>
                            <option value="0">Черновик</option>
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
                        <div v-for="gallery in filteredGalleries" :key="gallery.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedGalleries" :value="gallery.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link :href="`/admin/galleries/${gallery.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ gallery.title }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ gallery.id }}</div>
                                    <div class="text-xs text-slate-400">Тип: {{ gallery.type }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span class="text-slate-500">Изображений: {{ gallery.images_count || 0 }}</span>
                                        <span :class="gallery.status ? 'text-emerald-600' : 'text-slate-500'">
                                            {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                                        </span>
                                        <span class="text-slate-400">Дата: {{ formatDate(gallery.created_at) }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <code class="text-xs bg-slate-100 px-2 py-0.5 rounded">[gallery id="{{ gallery.id }}"]</code>
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
                                <th class="col-title">Название</th>
                                <th class="col-type">Тип</th>
                                <th class="col-images">Изображений</th>
                                <th class="col-status">Статус</th>
                                <th class="col-shortcode">Шорткод</th>
                                <th class="col-created">Дата создания</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="gallery in filteredGalleries"
                                :key="gallery.id"
                                :class="{ 'bg-blue-50/50': selectedGalleries.includes(gallery.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox" @click.stop="toggleSelect(gallery.id)">
                                    <input
                                        type="checkbox"
                                        :checked="selectedGalleries.includes(gallery.id)"
                                        @change="toggleSelect(gallery.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(gallery.id)">
                                    <Link :href="`/admin/galleries/${gallery.id}/edit`" class="title-text" style="display: inline-block !important;" @click.stop>
                                        {{ gallery.title }}
                                    </Link>
                                </td>

                                <!-- Тип -->
                                <td class="col-type" @click="toggleSelect(gallery.id)">
                                    <span class="gallery-type-badge">
                                        {{ gallery.type }}
                                    </span>
                                </td>

                                <!-- Изображений -->
                                <td class="col-images" @click="toggleSelect(gallery.id)">
                                    <span class="stat-badge stat-badge-blue">
                                        {{ gallery.images_count || 0 }}
                                    </span>
                                </td>

                                <!-- Статус -->
                                <td class="col-status" @click="toggleSelect(gallery.id)">
                                    <span
                                        class="status-badge"
                                        :class="gallery.status ? 'status-published' : 'status-draft'"
                                    >
                                        {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                                    </span>
                                </td>

                                <!-- Шорткод -->
                                <td class="col-shortcode" @click="toggleSelect(gallery.id)">
                                    <code class="shortcode">[gallery id="{{ gallery.id }}"]</code>
                                </td>

                                <!-- Дата создания -->
                                <td class="col-created" @click="toggleSelect(gallery.id)">
                                    {{ formatDate(gallery.created_at) }}
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(gallery.id)">{{ gallery.id }}</td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="filteredGalleries.length === 0">
                                <td colspan="8" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет галерей
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первую галерею</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="filteredGalleries.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="pagination.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ pagination.current_page }} / {{ pagination.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="pagination.current_page === pagination.last_page"
                                class="admin-pagination-btn"
                            >
                                Вперед →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка создания галереи -->
        <GalleryModal
            :show="modalOpen"
            @close="modalOpen = false"
            @save="handleCreateGallery"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '../../../layouts/AdminLayout.vue';
import Toast from '../../../components/shared/Toast.vue';
import GalleryModal from './components/GalleryModal.vue';

const props = defineProps<{
    user: any;
    title: string;
}>();

const galleries = ref<any[]>([]);
const selectedGalleries = ref<number[]>([]);
const search = ref('');
const filterType = ref('');
const filterStatus = ref('');
const loading = ref(false);
const modalOpen = ref(false);

// Пагинация
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
});
const perPage = ref(20);

let searchTimeout: any = null;

const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const filteredGalleries = computed(() => {
    let items = galleries.value;

    if (search.value) {
        const q = search.value.toLowerCase();
        items = items.filter(g => g.title.toLowerCase().includes(q));
    }

    if (filterType.value) {
        items = items.filter(g => g.type === filterType.value);
    }

    if (filterStatus.value !== '') {
        items = items.filter(g => g.status === (filterStatus.value === '1'));
    }

    return items;
});

const allSelected = computed({
    get: () => filteredGalleries.value.length > 0 && selectedGalleries.value.length === filteredGalleries.value.length,
    set: (value: boolean) => {
        if (value) {
            selectedGalleries.value = filteredGalleries.value.map(g => g.id);
        } else {
            selectedGalleries.value = [];
        }
    }
});

const toggleSelect = (id: number) => {
    const index = selectedGalleries.value.indexOf(id);
    if (index === -1) {
        selectedGalleries.value.push(id);
    } else {
        selectedGalleries.value.splice(index, 1);
    }
};

const loadGalleries = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/galleries/list');
        galleries.value = response.data;
        pagination.value.total = response.data.length;
        pagination.value.last_page = Math.ceil(pagination.value.total / perPage.value) || 1;
        pagination.value.from = 1;
        pagination.value.to = response.data.length;
    } catch (error) {
        console.error('Error loading galleries:', error);
    } finally {
        loading.value = false;
    }
};

const openCreateModal = () => {
    modalOpen.value = true;
};

const handleCreateGallery = async (data: any) => {
    try {
        const response = await axios.post('/admin/galleries', {
            title: data.title,
            type: data.type,
            status: data.status,
            settings: {},
        });

        modalOpen.value = false;
        showNotification('Галерея создана', 'success');
        router.visit(`/admin/galleries/${response.data.id}/edit`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
};

const applyFilters = () => {
    // Фильтры применяются через computed
};

const resetFilters = () => {
    search.value = '';
    filterType.value = '';
    filterStatus.value = '';
    selectedGalleries.value = [];
};

const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => {
        notification.value.show = false;
    }, 5000);
};

const deleteSelected = async () => {
    if (selectedGalleries.value.length === 0) return;
    if (!confirm(`Удалить ${selectedGalleries.value.length} галерей?`)) return;

    try {
        for (const id of selectedGalleries.value) {
            await axios.delete(`/admin/galleries/${id}`);
        }
        showNotification('Галереи удалены', 'success');
        selectedGalleries.value = [];
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка при удалении', 'error');
    }
};

const publishSelected = async () => {
    if (selectedGalleries.value.length === 0) return;

    try {
        for (const id of selectedGalleries.value) {
            const gallery = galleries.value.find(g => g.id === id);
            if (gallery) {
                await axios.put(`/admin/galleries/${id}`, { ...gallery, status: true });
            }
        }
        showNotification('Галереи опубликованы', 'success');
        selectedGalleries.value = [];
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка', 'error');
    }
};

const unpublishSelected = async () => {
    if (selectedGalleries.value.length === 0) return;

    try {
        for (const id of selectedGalleries.value) {
            const gallery = galleries.value.find(g => g.id === id);
            if (gallery) {
                await axios.put(`/admin/galleries/${id}`, { ...gallery, status: false });
            }
        }
        showNotification('Галереи сняты с публикации', 'success');
        selectedGalleries.value = [];
        await loadGalleries();
    } catch (error) {
        showNotification('Ошибка', 'error');
    }
};

const prevPage = () => {
    // Для локальной пагинации
};

const nextPage = () => {
    // Для локальной пагинации
};

onMounted(() => {
    loadGalleries();
});
</script>
