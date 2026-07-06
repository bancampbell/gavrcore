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
                        href="/admin/users/create"
                        class="admin-btn admin-btn-primary no-style"
                    >
                        + Создать пользователя
                    </Link>
                    <template v-if="selectedUsers.length > 0">
                        <button
                            @click="bulkBlock"
                            class="admin-btn admin-btn-danger"
                        >
                            Заблокировать
                        </button>
                        <button
                            @click="bulkUnblock"
                            class="admin-btn admin-btn-secondary"
                        >
                            Разблокировать
                        </button>
                        <button
                            @click="openDeleteModalForSelected"
                            class="admin-btn admin-btn-secondary"
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
                            placeholder="Введите имя, логин или email..."
                            class="admin-filter-input"
                        />
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select
                            v-model="filters.blocked"
                            @change="applyFilters"
                            class="admin-filter-select"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="false">Активные</option>
                            <option :value="true">Заблокированные</option>
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
                        <div v-for="user in users.data" :key="user.id" class="p-4 hover:bg-slate-50">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" v-model="selectedUsers" :value="user.id" class="mt-1 admin-checkbox">
                                <div class="flex-1">
                                    <Link :href="`/admin/users/${user.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                                        {{ user.name }}
                                    </Link>
                                    <div class="text-sm text-slate-500 mt-1">ID: {{ user.id }}</div>
                                    <div class="text-xs text-slate-400">Логин: {{ user.username }}</div>
                                    <div class="text-xs text-slate-400">Email: {{ user.email }}</div>
                                    <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                        <span :class="user.blocked ? 'text-rose-600' : 'text-emerald-600'">
                                            {{ user.blocked ? 'Заблокирован' : 'Активен' }}
                                        </span>
                                        <span class="text-slate-500">Активирован: {{ user.activated ? 'Да' : 'Нет' }}</span>
                                        <span class="text-slate-500" v-if="user.last_login_at">Последний вход: {{ formatDate(user.last_login_at) }}</span>
                                        <span class="text-slate-500">Дата регистрации: {{ formatDate(user.created_at) }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <span v-for="group in user.groups" :key="group.id" class="inline-flex px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-600 mr-1">
                                            {{ group.name }}
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
                                <th class="col-title">Имя</th>
                                <th class="col-username">Логин</th>
                                <th class="col-status">Статус</th>
                                <th class="col-activated">Активация</th>
                                <th class="col-email">E-mail</th>
                                <th class="col-groups">Группы</th>
                                <th class="col-lastlogin">Последний вход</th>
                                <th class="col-id">ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="cursor-pointer"
                                :class="{ 'bg-blue-50/50': selectedUsers.includes(user.id) }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox">
                                    <input
                                        type="checkbox"
                                        :checked="selectedUsers.includes(user.id)"
                                        @change="toggleSelect(user.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Название -->
                                <td class="col-title" @click="toggleSelect(user.id)">
                                    <Link :href="`/admin/users/${user.id}/edit`" class="title-text" style="display: inline-block !important;" @click.stop>
                                        {{ user.name }}
                                    </Link>
                                </td>

                                <!-- Логин -->
                                <td class="col-username" @click="toggleSelect(user.id)">{{ user.username }}</td>

                                <!-- Статус - единый класс -->
                                <td class="col-status" @click="toggleSelect(user.id)">
                                    <span :class="user.blocked ? 'status-badge status-inactive' : 'status-badge status-active'">
                                        {{ user.blocked ? 'Заблокирован' : 'Активен' }}
                                    </span>
                                </td>

                                <!-- Активация - единый класс -->
                                <td class="col-activated" @click="toggleSelect(user.id)">
                                    <span :class="user.activated ? 'status-badge status-active' : 'status-badge status-inactive'">
                                        {{ user.activated ? 'Активирован' : 'Не активирован' }}
                                    </span>
                                </td>

                                <!-- Email -->
                                <td class="col-email" @click="toggleSelect(user.id)" :title="user.email">
                                    {{ user.email }}
                                </td>

                                <!-- Группы -->
                                <td class="col-groups" @click="toggleSelect(user.id)">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="group in user.groups" :key="group.id" class="group-badge">
                                            {{ group.name }}
                                        </span>
                                        <span v-if="!user.groups?.length" class="text-slate-400 text-xs">—</span>
                                    </div>
                                </td>

                                <!-- Последний вход -->
                                <td class="col-lastlogin" @click="toggleSelect(user.id)">
                                    {{ formatDate(user.last_login_at) || '—' }}
                                </td>

                                <!-- ID -->
                                <td class="col-id" @click="toggleSelect(user.id)">{{ user.id }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="users.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ users.from || 0 }} - {{ users.to || 0 }} из {{ users.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="users.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ users.current_page }} / {{ users.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="users.current_page === users.last_page"
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
            title="Удаление пользователя"
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
import { useUsers } from '@/composables/useUsers';

interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    blocked: boolean;
    activated: boolean;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string;
    groups: { id: number; name: string }[];
}

interface UsersData {
    data: User[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

interface Group {
    id: number;
    name: string;
}

interface Filters {
    search?: string;
    blocked?: boolean;
}

const props = defineProps<{
    user: any;
    title?: string;
    users: UsersData;
    groups: Group[];
    filters?: Filters;
}>();

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const toggleSelect = (id: number) => {
    const index = selectedUsers.value.indexOf(id);
    if (index === -1) {
        selectedUsers.value.push(id);
    } else {
        selectedUsers.value.splice(index, 1);
    }
};

const {
    filters,
    selectedUsers,
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
    bulkBlock,
    bulkUnblock,
    confirmDeleteHandler,
    confirmBulkAction,
    showNotification,
} = useUsers(props);

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
