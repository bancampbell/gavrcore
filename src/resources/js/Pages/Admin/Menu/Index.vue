<template>
    <AdminLayout :user="user">
        <div class="bg-white rounded-lg shadow">
            <!-- Фиксированная панель с кнопками -->
            <div class="sticky top-12 z-10 bg-white border-b border-gray-200 px-6 py-3">
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="openCreateModal"
                        class="bg-[#46a546] text-white px-4 py-2 rounded-md text-sm hover:bg-[#3d8a3d] transition"
                    >
                        + Создать тип меню
                    </button>
                    <button
                        @click="openEditSelectedModal"
                        :disabled="selectedMenuTypes.length !== 1"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Редактировать выбранный
                    </button>
                    <button
                        @click="bulkPublish"
                        :disabled="selectedMenuTypes.length === 0"
                        class="px-4 py-2 rounded-md text-sm bg-green-600 text-white hover:bg-green-700 transition disabled:opacity-50"
                    >
                        Опубликовать
                    </button>
                    <button
                        @click="bulkUnpublish"
                        :disabled="selectedMenuTypes.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-red-500 bg-white text-red-600 hover:bg-red-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Снять с публикации
                    </button>
                    <button
                        @click="bulkDelete"
                        :disabled="selectedMenuTypes.length === 0"
                        class="px-4 py-2 rounded-md text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Удалить выбранные
                    </button>
                </div>

                <!-- Фильтры -->
                <div class="mt-3 flex flex-wrap gap-4 items-end">
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
                    <div class="min-w-[150px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm"
                        >
                            <option :value="undefined">Все</option>
                            <option :value="true">Опубликовано</option>
                            <option :value="false">Не опубликовано</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                        Очистить
                    </button>
                </div>
            </div>

            <!-- Grid таблица -->
            <div class="hidden lg:block overflow-x-auto">
                <!-- Заголовки -->
                <div class="grid bg-gray-50 border-b border-gray-200 px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider"
                     style="grid-template-columns: 40px 60px 1fr 1.5fr 80px 120px">
                    <div class="flex items-center">
                        <input type="checkbox" v-model="allSelected" class="rounded border-gray-300">
                    </div>
                    <div>ID</div>
                    <div>Название</div>
                    <div>Алиас</div>
                    <div class="text-center">Пунктов</div>
                    <div class="text-center">Статус</div>
                </div>

                <!-- Строки -->
                <div v-for="(type, index) in menuTypes.data" :key="type.id"
                     class="grid px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100"
                     :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                     style="grid-template-columns: 40px 60px 1fr 1.5fr 80px 120px">

                    <div class="flex items-center">
                        <input type="checkbox" v-model="selectedMenuTypes" :value="type.id" class="rounded border-gray-300">
                    </div>

                    <div class="flex items-center text-gray-600">{{ type.id }}</div>

                    <div class="flex items-center">
                        <button
                            @click="goToMenuItems(type.id, type.title)"
                            class="font-medium text-[#3071a9] hover:underline cursor-pointer"
                        >
                            {{ type.title }}
                        </button>
                    </div>

                    <div class="flex items-center text-gray-600">{{ type.alias }}</div>

                    <div class="flex items-center justify-center">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-medium">
                            {{ type.items_count || 0 }}
                        </span>
                    </div>

                    <div class="flex items-center justify-center">
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-full font-medium',
                                type.status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-700'
                            ]"
                        >
                            {{ type.status ? 'Опубликовано' : 'Не опубликовано' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Мобильная версия - карточки -->
            <div class="lg:hidden divide-y divide-gray-100">
                <div v-for="type in menuTypes.data" :key="type.id" class="p-4 hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" v-model="selectedMenuTypes" :value="type.id" class="mt-1 rounded border-gray-300">
                        <div class="flex-1">
                            <button
                                @click="goToMenuItems(type.id, type.title)"
                                class="font-medium text-[#3071a9] hover:underline"
                            >
                                {{ type.title }}
                            </button>
                            <div class="text-sm text-gray-500 mt-1">Алиас: {{ type.alias }}</div>
                            <div class="flex flex-wrap gap-4 mt-2 text-xs">
                                <span class="text-gray-500">ID: {{ type.id }}</span>
                                <span class="text-blue-600">Пунктов: {{ type.items_count || 0 }}</span>
                                <span
                                    :class="type.status ? 'text-green-600' : 'text-red-600'"
                                >
                                    {{ type.status ? 'Опубликовано' : 'Не опубликовано' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="border-t border-gray-200 px-6 py-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Показано {{ menuTypes.from || 0 }} - {{ menuTypes.to || 0 }} из {{ menuTypes.total || 0 }}
                </div>
                <div class="flex gap-1">
                    <button
                        @click="prevPage"
                        :disabled="menuTypes.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        ← Назад
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-600">
                        {{ menuTypes.current_page }} / {{ menuTypes.last_page }}
                    </span>
                    <button
                        @click="nextPage"
                        :disabled="menuTypes.current_page === menuTypes.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm disabled:opacity-50 hover:bg-gray-50"
                    >
                        Вперед →
                    </button>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания/редактирования -->
        <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="modalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-xl font-bold text-gray-900">{{ editingId ? 'Редактировать тип меню' : 'Создать тип меню' }}</h3>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Название *</label>
                        <input
                            v-model="form.title"
                            type="text"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Алиас</label>
                        <input
                            v-model="form.alias"
                            type="text"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="останется пустым - сгенерируется автоматически"
                        />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Описание</label>
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        ></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2">
                            <input
                                v-model="form.status"
                                type="checkbox"
                                class="h-4 w-4 text-indigo-600 rounded border-gray-300"
                            />
                            <span class="text-gray-700">Активно</span>
                        </label>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="flex-1 bg-indigo-600 text-white py-2 rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ loading ? 'Сохранение...' : (editingId ? 'Обновить' : 'Создать') }}
                        </button>
                        <button
                            type="button"
                            @click="modalOpen = false"
                            class="flex-1 bg-gray-200 text-gray-700 py-2 rounded-xl font-medium hover:bg-gray-300"
                        >
                            Отмена
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление типа меню"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDeleteHandler"
        />

        <!-- Toast уведомление -->
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useMenuTypes } from '@/composables/useMenuTypes';
import { router } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
}

interface MenuType {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    ordering: number;
    status: boolean;
    items_count?: number;
}

interface MenuTypesData {
    data: MenuType[];
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
    user: User;
    menuTypes: MenuTypesData;
    filters?: Filters;
}>();

const goToMenuItems = (menuTypeId: number, title: string) => {
    router.visit(`/admin/menu/types/${menuTypeId}/items`, {
        props: { menuTypeId, menuTypeTitle: title }
    });
};

const {
    filters,
    selectedMenuTypes,
    allSelected,
    notification,
    modalOpen,
    editingId,
    loading,
    deleteModalOpen,
    deleteLoading,
    form,
    deleteMessage,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    openCreateModal,
    openEditSelectedModal,
    submitForm,
    bulkDelete,
    bulkPublish,
    bulkUnpublish,
    confirmDeleteHandler
} = useMenuTypes(props);
</script>
