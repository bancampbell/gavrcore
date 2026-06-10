<template>
    <AdminLayout :user="user">
        <div class="bg-white rounded-lg shadow">
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <Link
                        href="/admin/groups/create"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать группу
                    </Link>
                    <button
                        @click="bulkPublish"
                        :disabled="selectedGroups.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="bulkUnpublish"
                        :disabled="selectedGroups.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-red-500 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50"
                    >
                        Снять с публикации
                    </button>
                    <button
                        @click="openDeleteModalForSelected"
                        :disabled="selectedGroups.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50"
                    >
                        Удалить выбранные
                    </button>
                </div>
            </div>

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
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Скрыто</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="group in groups.data" :key="group.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedGroups" :value="group.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <div class="font-medium text-[#3071a9]">{{ group.name }}</div>
                            <div class="text-sm text-gray-500 mt-1">ID: {{ group.id }}</div>
                            <div class="text-xs text-gray-400">Алиас: {{ group.alias }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span :class="group.status ? 'text-green-600' : 'text-red-600'">
                                    {{ group.status ? 'Опубликовано' : 'Скрыто' }}
                                </span>
                                <span class="text-gray-500" v-if="group.description">Описание: {{ group.description }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block overflow-x-auto">
                <div class="flex bg-gray-50 border-b border-gray-200 px-4 py-3 text-sm font-medium text-gray-500">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">ID</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Название</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Алиас</div>
                    <div class="flex-1 flex items-center justify-start font-bold text-[#3071a9]">Описание</div>
                    <div class="flex-1 flex items-center justify-center font-bold text-[#3071a9]">Статус</div>
                </div>

                <div v-for="(group, index) in groups.data" :key="group.id"
                     class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                    <div class="w-10 flex items-center justify-center">
                        <input type="checkbox" v-model="selectedGroups" :value="group.id" class="rounded border-gray-300">
                    </div>
                    <div class="flex-1 flex items-center justify-center text-gray-600">{{ group.id }}</div>
                    <div class="flex-1 flex items-start">
                        <Link :href="`/admin/groups/${group.id}/edit`" class="font-medium text-[#3071a9] hover:underline">
                            {{ group.name }}
                        </Link>
                    </div>
                    <div class="flex-1 flex items-start text-gray-600">{{ group.alias }}</div>
                    <div class="flex-1 flex items-start text-gray-500 truncate" :title="group.description">
                        {{ group.description || '—' }}
                    </div>
                    <div class="flex-1 flex items-center justify-center">
                        <span :class="group.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'" class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap">
                            {{ group.status ? 'Опубликовано' : 'Скрыто' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ groups.from || 0 }} - {{ groups.to || 0 }} из {{ groups.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="groups.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ groups.current_page }} / {{ groups.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="groups.current_page === groups.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

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
    groups: GroupsData;
    filters?: Filters;
}>();

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
