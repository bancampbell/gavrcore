<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full">
            <!-- Панель действий + фильтры (sticky) -->
            <div class="admin-page-actions flex-shrink-0">
                <h1 class="admin-page-title">Уровни доступа</h1>
                <div class="flex flex-wrap gap-2.5">
                    <Link
                        href="/admin/access-levels/create"
                        class="admin-btn admin-btn-primary no-style"
                    >
                        + Создать уровень доступа
                    </Link>
                    <template v-if="selectedLevels.length > 0">
                        <button
                            @click="openBulkDeleteModal"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить выбранные
                        </button>
                        <button
                            @click="bulkActivate"
                            class="admin-btn admin-btn-secondary"
                        >
                            Активировать
                        </button>
                        <button
                            @click="bulkDeactivate"
                            class="admin-btn admin-btn-secondary"
                        >
                            Деактивировать
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
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="admin-filter-select"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="true">Активные</option>
                            <option :value="false">Неактивные</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card">
                    <!-- Мобильная версия (карточки) -->
                    <div class="lg:hidden divide-y divide-slate-100">
                        <div v-for="level in filteredLevels" :key="level.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input
                                    type="checkbox"
                                    v-model="selectedLevels"
                                    :value="level.id"
                                    class="mt-1 admin-checkbox"
                                />
                                <div class="flex-1">
                                    <Link :href="`/admin/access-levels/${level.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ level.title }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ level.id }}</div>
                                    <div class="text-xs text-slate-400">Алиас: {{ level.alias }}</div>
                                    <div class="mt-1">
                                        <span class="text-xs text-slate-500">Статус:</span>
                                        <span :class="level.status ? 'text-emerald-600' : 'text-rose-600'" class="ml-1">
                                            {{ level.status ? 'Активен' : 'Неактивен' }}
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="text-xs text-slate-500">Группы:</span>
                                        <div class="flex flex-wrap gap-1 mt-0.5">
                                            <span v-for="group in level.groups" :key="group.id" class="group-badge">
                                                {{ group.name }}
                                            </span>
                                            <span v-if="!level.groups?.length" class="text-xs text-slate-400">—</span>
                                        </div>
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
                                <th class="col-id">ID</th>
                                <th class="col-title">Название уровня</th>
                                <th class="col-alias">Алиас</th>
                                <th class="col-status">Статус</th>
                                <th class="col-groups">Группы пользователей</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="level in filteredLevels"
                                :key="level.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedLevels.includes(level.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedLevels.includes(level.id)"
                                        @change="toggleSelect(level.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(level.id)">{{ level.id }}</td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(level.id)">
                                    <Link
                                        :href="`/admin/access-levels/${level.id}/edit`"
                                        class="title-text"
                                        style="display: inline-block !important;"
                                        @click.stop
                                    >
                                        {{ level.title }}
                                    </Link>
                                </td>

                                <!-- Алиас -->
                                <td class="col-alias" @click="toggleSelect(level.id)">{{ level.alias }}</td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(level.id)">
                                    <span
                                        class="status-badge"
                                        :class="level.status ? 'status-active' : 'status-inactive'"
                                    >
                                        {{ level.status ? 'Активен' : 'Неактивен' }}
                                    </span>
                                </td>

                                <!-- Группы -->
                                <td class="col-groups" @click="toggleSelect(level.id)">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="group in level.groups" :key="group.id" class="group-badge">
                                            {{ group.name }}
                                        </span>
                                        <span v-if="!level.groups?.length" class="text-slate-400 text-xs">—</span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="filteredLevels.length === 0">
                                <td colspan="6" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет уровней доступа
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первый уровень доступа</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="filteredLevels.length > 0" class="admin-pagination">
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

        <!-- Модалки -->
        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление уровня доступа"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDelete"
        />

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

        <ConfirmModal
            :is-open="toggleStatusModalOpen"
            title="Изменение статуса"
            :message="toggleStatusMessage"
            confirm-text="Подтвердить"
            type="warning"
            :loading="loading"
            @close="toggleStatusModalOpen = false"
            @confirm="confirmToggleStatus"
        />

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
const perPage = ref(20);
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

// Обновление пагинации
const updatePagination = () => {
    pagination.value.total = filteredLevels.value.length;
    pagination.value.last_page = Math.ceil(pagination.value.total / perPage.value) || 1;
    pagination.value.from = (pagination.value.current_page - 1) * perPage.value + 1;
    pagination.value.to = Math.min(pagination.value.current_page * perPage.value, pagination.value.total);
};

// Пагинированные уровни
const paginatedLevels = computed(() => {
    const start = (pagination.value.current_page - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredLevels.value.slice(start, end);
});

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
const loading = ref(false);
const deleteModalOpen = ref(false);
const deleteLoading = ref(false);
const itemToDelete = ref<AccessLevel | null>(null);
const deleteMessage = ref('');

const bulkDeleteModalOpen = ref(false);
const bulkDeleteLoading = ref(false);
const bulkDeleteMessage = ref('');

const toggleStatusModalOpen = ref(false);
const toggleStatusMessage = ref('');
const toggleStatusData = ref<{ id: number; status: boolean } | null>(null);

let searchTimeout: any = null;

const showNotification = (message: string, type: string = 'success') => {
    notification.value = { show: true, message, type };
    setTimeout(() => { notification.value.show = false; }, 3000);
};

const toggleSelect = (id: number) => {
    const index = selectedLevels.value.indexOf(id);
    if (index === -1) {
        selectedLevels.value.push(id);
    } else {
        selectedLevels.value.splice(index, 1);
    }
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
        await applyFilters();
        showNotification('Уровень доступа удалён', 'success');
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
    bulkDeleteLoading.value = true;
    try {
        for (const id of selectedLevels.value) {
            await axios.delete(`/admin/access-levels/${id}`);
        }
        const count = selectedLevels.value.length;
        selectedLevels.value = [];
        bulkDeleteModalOpen.value = false;
        await applyFilters();
        showNotification(`${count} уровней доступа удалено`, 'success');
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    } finally {
        bulkDeleteLoading.value = false;
    }
};

// Переключение статуса (одиночное)
const toggleStatus = (level: AccessLevel) => {
    const newStatus = !level.status;
    toggleStatusData.value = { id: level.id, status: newStatus };
    toggleStatusMessage.value = `Вы уверены, что хотите ${newStatus ? 'активировать' : 'деактивировать'} уровень "${level.title}"?`;
    toggleStatusModalOpen.value = true;
};

const confirmToggleStatus = async () => {
    if (!toggleStatusData.value) return;
    loading.value = true;
    try {
        if (toggleStatusData.value.id === 0) {
            // Массовая операция
            const ids = selectedLevels.value;
            for (const id of ids) {
                await axios.patch(`/admin/access-levels/${id}/status`);
            }
            // Обновляем локально
            filteredLevels.value.forEach(level => {
                if (ids.includes(level.id)) {
                    level.status = toggleStatusData.value?.status ?? false;
                }
            });
            selectedLevels.value = [];
            showNotification(`${ids.length} уровней доступа ${toggleStatusData.value.status ? 'активировано' : 'деактивировано'}`, 'success');
        } else {
            // Одиночная операция
            await axios.patch(`/admin/access-levels/${toggleStatusData.value.id}/status`);
            const level = filteredLevels.value.find(l => l.id === toggleStatusData.value?.id);
            if (level) {
                level.status = toggleStatusData.value.status;
            }
            showNotification(`Уровень доступа ${toggleStatusData.value.status ? 'активирован' : 'деактивирован'}`, 'success');
        }
        toggleStatusModalOpen.value = false;
        updatePagination();
    } catch (error: any) {
        showNotification(error.response?.data?.message || 'Ошибка', 'error');
    } finally {
        loading.value = false;
        toggleStatusData.value = null;
    }
};

// Массовые операции со статусом
const bulkActivate = () => {
    if (selectedLevels.value.length === 0) return;
    bulkToggleStatus(true);
};

const bulkDeactivate = () => {
    if (selectedLevels.value.length === 0) return;
    bulkToggleStatus(false);
};

const bulkToggleStatus = (status: boolean) => {
    const action = status ? 'активировать' : 'деактивировать';
    toggleStatusMessage.value = `Вы уверены, что хотите ${action} ${selectedLevels.value.length} уровень(ей) доступа?`;
    toggleStatusData.value = { id: 0, status };
    toggleStatusModalOpen.value = true;
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
