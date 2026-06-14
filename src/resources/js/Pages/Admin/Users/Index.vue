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
                        href="/admin/users/create"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать пользователя
                    </Link>
                    <button
                        @click="bulkBlock"
                        :disabled="selectedUsers.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-red-600 text-white hover:bg-red-700 transition disabled:opacity-50"
                    >
                        Заблокировать
                    </button>
                    <button
                        @click="bulkUnblock"
                        :disabled="selectedUsers.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-green-500 bg-white text-green-600 hover:bg-green-50 transition disabled:opacity-50"
                    >
                        Разблокировать
                    </button>
                    <button
                        @click="openDeleteModalForSelected"
                        :disabled="selectedUsers.length === 0"
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
                            placeholder="Введите имя, логин или email..."
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
                        <select
                            v-model="filters.blocked"
                            @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="false">Активные</option>
                            <option :value="true">Заблокированные</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Список карточек (мобильная версия) -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="user in users.data" :key="user.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedUsers" :value="user.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <div class="font-medium text-[#3071a9]">{{ user.name }}</div>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ user.id }}</div>
                            <div class="text-xs text-gray-400">Логин: {{ user.username }}</div>
                            <div class="text-xs text-gray-400">Email: {{ user.email }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span :class="user.blocked ? 'text-red-600' : 'text-green-600'">
                                    {{ user.blocked ? 'Заблокирован' : 'Активен' }}
                                </span>
                                <span class="text-gray-500">Активирован: {{ user.activated ? 'Да' : 'Нет' }}</span>
                                <span class="text-gray-500" v-if="user.last_login_at">Последний вход: {{ formatDate(user.last_login_at) }}</span>
                                <span class="text-gray-500">Дата регистрации: {{ formatDate(user.created_at) }}</span>
                            </div>
                            <div class="mt-1">
                                <span v-for="group in user.groups" :key="group.id" class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600 mr-1">
                                    {{ group.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Десктопная версия (Flexbox таблица) -->
            <div class="hidden lg:block overflow-x-auto">
                <div class="flex bg-gray-50 border-b border-gray-200 px-4 py-3 text-sm font-medium text-gray-500">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Имя</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Логин</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Статус</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Активация</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Группы</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">E-mail</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Последний вход</div>
                    <div class="w-16 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                </div>

                <div v-for="(user, index) in users.data" :key="user.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedUsers" :value="user.id" class="rounded border-gray-300">
                    </div>
                    <div class="flex-1 flex items-start">
                        <Link :href="`/admin/users/${user.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                            {{ user.name }}
                        </Link>
                    </div>
                    <div class="flex-1 flex items-start text-gray-600">{{ user.username }}</div>
                    <div class="flex-1 flex items-center justify-center">
                        <span :class="user.blocked ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-800'" class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap">
                            {{ user.blocked ? 'Заблокирован' : 'Активен' }}
                        </span>
                    </div>
                    <div class="flex-1 flex items-center justify-center">
                        <span :class="user.activated ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'" class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap">
                            {{ user.activated ? 'Активирован' : 'Не активирован' }}
                        </span>
                    </div>
                    <div class="flex-1 flex items-start">
                        <div class="flex flex-wrap gap-1">
                            <span v-for="group in user.groups" :key="group.id" class="inline-flex px-1.5 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                                {{ group.name }}
                            </span>
                            <span v-if="!user.groups?.length" class="text-gray-400 text-xs">—</span>
                        </div>
                    </div>
                    <div class="flex-1 flex items-start text-gray-600 truncate" :title="user.email">
                        {{ user.email }}
                    </div>
                    <div class="flex-1 flex items-center justify-center text-gray-600 whitespace-nowrap">
                        {{ formatDate(user.last_login_at) || '—' }}
                    </div>
                    <div class="w-16 flex items-center justify-center text-gray-600">{{ user.id }}</div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ users.from || 0 }} - {{ users.to || 0 }} из {{ users.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="users.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ users.current_page }} / {{ users.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="users.current_page === users.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
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

        <!-- Модальное окно для массовых операций -->
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
