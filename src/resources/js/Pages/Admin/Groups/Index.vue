<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full">
            <!-- Панель действий + фильтры (sticky) -->
            <div class="admin-page-actions flex-shrink-0">
                <div class="flex flex-wrap gap-2.5">
                    <Link
                        href="/admin/groups/create"
                        class="admin-btn admin-btn-primary no-style"
                    >
                        + Создать группу
                    </Link>
                    <template v-if="selectedGroups.length > 0">
                        <button
                            @click="bulkPublish"
                            class="admin-btn admin-btn-secondary"
                        >
                            Опубликовать
                        </button>
                        <button
                            @click="bulkUnpublish"
                            class="admin-btn admin-btn-secondary"
                        >
                            Снять с публикации
                        </button>
                        <button
                            @click="openDeleteModalForSelected"
                            class="admin-btn admin-btn-danger"
                        >
                            Удалить выбранные
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
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Скрыто</option>
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
                        <div v-for="group in groups.data" :key="group.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedGroups" :value="group.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link :href="`/admin/groups/${group.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ group.name }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ group.id }}</div>
                                    <div class="text-xs text-slate-400">Алиас: {{ group.alias }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span :class="group.status ? 'text-emerald-600' : 'text-rose-600'">
                                            {{ group.status ? 'Опубликовано' : 'Скрыто' }}
                                        </span>
                                        <span class="text-slate-500" v-if="group.description">Описание: {{ group.description }}</span>
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
                                <th class="col-title">Название</th>
                                <th class="col-alias">Алиас</th>
                                <th class="col-description">Описание</th>
                                <th class="col-status">Статус</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="group in groups.data"
                                :key="group.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedGroups.includes(group.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedGroups.includes(group.id)"
                                        @change="toggleSelect(group.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(group.id)">{{ group.id }}</td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(group.id)">
                                    <Link
                                        :href="`/admin/groups/${group.id}/edit`"
                                        class="title-text"
                                        style="display: inline-block !important;"
                                        @click.stop
                                    >
                                        {{ group.name }}
                                    </Link>
                                </td>

                                <!-- Алиас -->
                                <td class="col-alias" @click="toggleSelect(group.id)">{{ group.alias }}</td>

                                <!-- Описание -->
                                <td class="col-description" @click="toggleSelect(group.id)" :title="group.description || '—'">
                                    {{ group.description || '—' }}
                                </td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(group.id)">
                                    <span
                                        class="status-badge"
                                        :class="group.status ? 'status-published' : 'status-inactive'"
                                    >
                                        {{ group.status ? 'Опубликовано' : 'Скрыто' }}
                                    </span>
                                </td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="groups.data?.length === 0">
                                <td colspan="6" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет групп
                                    <p style="font-size: 12px; margin-top: 4px;">Создайте первую группу</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="groups.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ groups.from || 0 }} - {{ groups.to || 0 }} из {{ groups.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="groups.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ groups.current_page }} / {{ groups.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="groups.current_page === groups.last_page"
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
            title="Удаление группы"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDeleteHandler"
        />

        <ConfirmModal
            :is-open="bulkModalOpen"
            :title="bulkModalTitle"
            :message="bulkModalMessage"
            confirm-text="Подтвердить"
            type="danger"
            :loading="loading"
            @close="bulkModalOpen = false"
            @confirm="confirmBulkAction"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useGroups } from '@/composables/useGroups';

interface Group {
    id: number;
    name: string;
    alias: string;
    description: string | null;
    status: boolean;
    ordering: number;
}

interface GroupsData {
    data: Group[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Filters {
    search?: string;
    status?: boolean;
}

const props = defineProps<{
    user: any;
    title?: string;
    groups: GroupsData;
    filters?: Filters;
}>();

const toggleSelect = (id: number) => {
    const index = selectedGroups.value.indexOf(id);
    if (index === -1) {
        selectedGroups.value.push(id);
    } else {
        selectedGroups.value.splice(index, 1);
    }
};

const {
    filters,
    selectedGroups,
    allSelected,
    notification,
    loading,
    deleteModalOpen,
    deleteLoading,
    deleteMessage,
    bulkModalOpen,
    bulkModalTitle,
    bulkModalMessage,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    openDeleteModalForSelected,
    bulkDelete,
    bulkPublish,
    bulkUnpublish,
    confirmDeleteHandler,
    confirmBulkAction,
    showNotification,
} = useGroups(props);

onMounted(() => {
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
