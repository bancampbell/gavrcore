<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <Link
                        href="/admin/access-levels/create"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать уровень доступа
                    </Link>
                    <button
                        @click="openBulkDeleteModal"
                        :disabled="selectedLevels.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50"
                    >
                        Удалить выбранные
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
                        <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="true">Активные</option>
                            <option :value="false">Неактивные</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Список карточек (мобильная версия) -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="level in paginatedLevels" :key="level.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            v-model="selectedLevels"
                            :value="level.id"
                            class="mt-1 rounded border-gray-300"
                        />
                        <div class="flex-1">
                            <div class="font-medium text-[#3071a9]">{{ level.title }}</div>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ level.id }}</div>
                            <div class="text-xs text-gray-400">Алиас: {{ level.alias }}</div>
                            <div class="mt-1">
                                <span class="text-xs text-gray-500">Статус:</span>
                                <span :class="level.status ? 'text-green-600' : 'text-red-600'" class="ml-1">
                                    {{ level.status ? 'Активен' : 'Неактивен' }}
                                </span>
                            </div>
                            <div class="mt-1">
                                <span class="text-xs text-gray-500">Группы:</span>
                                <div class="flex flex-wrap gap-1 mt-0.5">
                                    <span v-for="group in level.groups" :key="group.id" class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                        {{ group.name }}
                                    </span>
                                    <span v-if="!level.groups?.length" class="text-xs text-gray-400">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/admin/access-levels/${level.id}/edit`" class="text-indigo-600 hover:text-indigo-800" title="Редактировать">✏️</Link>
                            <button @click="deleteLevel(level)" class="text-red-600 hover:text-red-800" title="Удалить">🗑</button>
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
                    <div class="w-16 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Название уровня</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Алиас</div>
                    <div class="w-24 flex items-center justify-center font-bold text-[#3071a9]">Статус</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Группы пользователей, имеющие право доступа</div>
                </div>

                <!-- Строки -->
                <div v-for="(level, index) in paginatedLevels" :key="level.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">

                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedLevels" :value="level.id" class="rounded border-gray-300">
                    </div>

                    <div class="w-16 flex items-center justify-center text-gray-600">{{ level.id }}</div>

                    <div class="flex-1 flex items-start">
                        <Link :href="`/admin/access-levels/${level.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                            {{ level.title }}
                        </Link>
                    </div>

                    <div class="flex-1 flex items-start text-gray-500 text-sm">{{ level.alias }}</div>

                    <div class="w-24 flex items-center justify-center">
                        <span :class="level.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'" class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap">
                            {{ level.status ? 'Активен' : 'Неактивен' }}
                        </span>
                    </div>

                    <div class="flex-1 flex items-start">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="group in level.groups" :key="group.id" class="inline-flex px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                {{ group.name }}
                            </span>
                            <span v-if="!level.groups?.length" class="text-gray-400 text-xs">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ pagination.current_page }} / {{ pagination.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно для массового удаления -->
        <ConfirmModal
            :is-open="bulkDeleteModalOpen"
            title="Массовое удаление"
            :message="bulkDeleteMessage"
            confirm-text="Удалить все"
            type="danger"
            :loading="bulkDeleteLoading"
            @close="bulkDeleteModalOpen = false"
            @confirm="confirmBulkDelete"
        />

        <!-- Модальное окно для одиночного удаления -->
        <ConfirmModal :is-open="deleteModalOpen" title="Удаление уровня доступа" :message="deleteMessage" confirm-text="Удалить" type="danger" @close="deleteModalOpen = false" @confirm="confirmDelete" />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import axios from 'axios';

interface Group {
    id: number;
    name: string;
}

interface AccessLevel {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    status: boolean;
    ordering: number;
    groups: Group[];
}

const props = defineProps<{
    user: any;
    title?: string;
    accessLevels: AccessLevel[];
    groups: Group[];
}>();

// Фильтры
const filters = ref({
    search: '',
    status: undefined as boolean | undefined,
});

// Выбранные элементы
const selectedLevels = ref<number[]>([]);

// Пагинация
const perPage = ref(10);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
    total: 0,
});

// Отфильтрованные уровни
const filteredLevels = computed(() => {
    let levels = [...props.accessLevels];

    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        levels = levels.filter(l =>
            l.title.toLowerCase().includes(search) ||
            l.alias.toLowerCase().includes(search)
        );
    }

    if (filters.value.status !== undefined) {
        levels = levels.filter(l => l.status === filters.value.status);
    }

    return levels;
});

// Пагинированные уровни
const paginatedLevels = computed(() => {
    const start = (pagination.value.current_page - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredLevels.value.slice(start, end);
});

// Обновление пагинации
const updatePagination = () => {
    pagination.value.total = filteredLevels.value.length;
    pagination.value.last_page = Math.ceil(pagination.value.total / perPage.value);
    pagination.value.from = (pagination.value.current_page - 1) * perPage.value + 1;
    pagination.value.to = Math.min(pagination.value.current_page * perPage.value, pagination.value.total);
};

// Выбрать все
const allSelected = computed({
    get: () => {
        if (paginatedLevels.value.length === 0) return false;
        return selectedLevels.value.length === paginatedLevels.value.length;
    },
    set: (val: boolean) => {
        if (val) {
            selectedLevels.value = paginatedLevels.value.map(l => l.id);
        } else {
            selectedLevels.value = [];
        }
    },
});

const notification = ref({ show: false, message: '', type: 'success' });
const deleteModalOpen = ref(false);
const deleteLoading = ref(false);
const itemToDelete = ref<AccessLevel | null>(null);
const deleteMessage = ref('');

// Массовое удаление
const bulkDeleteModalOpen = ref(false);
const bulkDeleteLoading = ref(false);
const bulkDeleteMessage = ref('');

let searchTimeout: any = null;

const showNotification = (message: string, type: string = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

const applyFilters = () => {
    pagination.value.current_page = 1;
    updatePagination();
};

const debounceSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const resetFilters = () => {
    filters.value = { search: '', status: undefined };
    applyFilters();
};

const prevPage = () => {
    if (pagination.value.current_page > 1) {
        pagination.value.current_page--;
        updatePagination();
    }
};

const nextPage = () => {
    if (pagination.value.current_page < pagination.value.last_page) {
        pagination.value.current_page++;
        updatePagination();
    }
};

const deleteLevel = (level: AccessLevel) => {
    itemToDelete.value = level;
    deleteMessage.value = `Удалить уровень доступа "${level.title}"?`;
    deleteModalOpen.value = true;
};

const confirmDelete = async () => {
    if (!itemToDelete.value) return;
    deleteLoading.value = true;
    try {
        await axios.delete(`/admin/access-levels/${itemToDelete.value.id}`);
        deleteModalOpen.value = false;
        window.location.href = '/admin/access-levels?message=Уровень+доступа+удалён';
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
        deleteLoading.value = false;
    }
};

const openBulkDeleteModal = () => {
    if (selectedLevels.value.length === 0) return;

    if (selectedLevels.value.length === 1) {
        const level = filteredLevels.value.find(l => l.id === selectedLevels.value[0]);
        if (level) {
            deleteLevel(level);
        }
    } else {
        bulkDeleteMessage.value = `Вы уверены, что хотите удалить ${selectedLevels.value.length} уровень(ей) доступа?`;
        bulkDeleteModalOpen.value = true;
    }
};

const confirmBulkDelete = async () => {
    const count = selectedLevels.value.length;
    bulkDeleteLoading.value = true;
    try {
        for (const id of selectedLevels.value) {
            await axios.delete(`/admin/access-levels/${id}`);
        }
        selectedLevels.value = [];
        bulkDeleteModalOpen.value = false;
        window.location.href = `/admin/access-levels?message=${count}+уровней+доступа+удалено`;
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
        bulkDeleteLoading.value = false;
    }
};

onMounted(() => {
    updatePagination();

    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) {
        showNotification(decodeURIComponent(message), 'success');
        const url = new URL(window.location.href);
        url.searchParams.delete('message');
        window.history.replaceState({}, '', url.toString());
    }
});
</script>
