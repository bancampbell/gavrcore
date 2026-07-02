<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="openCreateModal"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать галерею
                    </button>
                    <button
                        @click="deleteSelected"
                        :disabled="selectedGalleries.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-red-500 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Удалить
                    </button>
                    <button
                        @click="publishSelected"
                        :disabled="selectedGalleries.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="unpublishSelected"
                        :disabled="selectedGalleries.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
                    >
                        Снять с публикации
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
                            v-model="search"
                            @input="debounceSearch"
                            placeholder="Введите название..."
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Тип</label>
                        <select v-model="filterType" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option value="grid">Сетка</option>
                            <option value="slideshow">Слайд-шоу</option>
                            <option value="slider">Слайдер</option>
                            <option value="switcher">Switcher</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
                        <select v-model="filterStatus" @change="applyFilters" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option value="1">Опубликовано</option>
                            <option value="0">Черновик</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Мобильная версия (карточки) -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="gallery in filteredGalleries" :key="gallery.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedGalleries" :value="gallery.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <Link :href="`/admin/galleries/${gallery.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                {{ gallery.title }}
                            </Link>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ gallery.id }}</div>
                            <div class="text-xs text-gray-400">Тип: {{ gallery.type }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span class="text-gray-500">Изображений: {{ gallery.images_count || 0 }}</span>
                                <span :class="gallery.status ? 'text-green-600' : 'text-gray-500'">
                                    {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                                </span>
                                <span class="text-gray-400">Дата: {{ formatDate(gallery.created_at) }}</span>
                            </div>
                            <div class="mt-1">
                                <code class="text-xs bg-gray-100 px-2 py-0.5 rounded">[gallery id="{{ gallery.id }}"]</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Десктопная версия (Flexbox) -->
            <div class="hidden lg:block overflow-x-auto">
                <div class="flex bg-gray-50 border-b border-gray-200 px-4 py-3 text-sm font-medium text-gray-500">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Название</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Тип</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Изображений</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Статус</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Шорткод</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Дата создания</div>
                    <div class="w-16 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                </div>

                <div v-for="(gallery, index) in filteredGalleries" :key="gallery.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">

                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedGalleries" :value="gallery.id" class="rounded border-gray-300">
                    </div>

                    <div class="flex-1 flex items-center">
                        <Link :href="`/admin/galleries/${gallery.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                            {{ gallery.title }}
                        </Link>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded">
                            {{ gallery.type }}
                        </span>
                    </div>

                    <div class="flex-1 flex items-center justify-center text-gray-600">
                        {{ gallery.images_count || 0 }}
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap" :class="{
                            'bg-green-100 text-green-800': gallery.status,
                            'bg-gray-100 text-gray-600': !gallery.status
                        }">
                            {{ gallery.status ? 'Опубликовано' : 'Черновик' }}
                        </span>
                    </div>

                    <div class="flex-1 flex items-center justify-center">
                        <code class="text-xs bg-gray-100 px-2 py-0.5 rounded">[gallery id="{{ gallery.id }}"]</code>
                    </div>

                    <div class="flex-1 flex items-center justify-center text-gray-600 whitespace-nowrap">
                        {{ formatDate(gallery.created_at) }}
                    </div>

                    <div class="w-16 flex items-center justify-center text-gray-600">{{ gallery.id }}</div>
                </div>
            </div>

            <div v-if="filteredGalleries.length === 0" class="text-center py-12 text-gray-500">
                <p>Нет галерей</p>
                <p class="text-sm mt-1">Создайте первую галерею</p>
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

const loadGalleries = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/galleries/list');
        galleries.value = response.data;
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

        // Переход на страницу редактирования
        router.visit(`/admin/galleries/${response.data.id}/edit`);
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка при создании', 'error');
    }
};

const formatDate = (date: string | null) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU');
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

onMounted(() => {
    loadGalleries();
});
</script>
