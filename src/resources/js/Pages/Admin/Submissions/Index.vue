<template>
    <AdminLayout :user="user">
        <Head>
            <title>{{ title }}</title>
        </Head>

        <div class="flex flex-col h-full w-full">
            <!-- Панель действий + фильтры -->
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title flex items-center gap-3">
                    Обратная связь
                    <span
                        v-if="unreadCount > 0"
                        class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500 text-white"
                    >
                        {{ unreadCount }} новых
                    </span>
                </h1>
                <div class="flex flex-wrap gap-2.5">
                    <template v-if="selectedSubmissions.length > 0">
                        <button
                            @click="openViewModalForSelected"
                            :disabled="selectedSubmissions.length !== 1"
                            class="admin-btn admin-btn-secondary"
                        >
                            Просмотр
                        </button>
                        <button
                            @click="openMarkReadModal"
                            class="admin-btn admin-btn-secondary"
                        >
                            Отметить как прочитанное
                        </button>
                        <button
                            @click="openDeleteModalForSelected"
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
                            v-model="filters.search"
                            @input="debounceSearch"
                            placeholder="Поиск по содержимому или форме..."
                            class="admin-filter-input"
                        />
                    </div>
                    <div class="w-48">
                        <label class="admin-filter-label">Форма</label>
                        <select
                            v-model="filters.form_id"
                            @change="applyFilters"
                            class="admin-filter-select"
                        >
                            <option :value="undefined">Все формы</option>
                            <option
                                v-for="form in forms"
                                :key="form.id"
                                :value="form.id"
                            >
                                {{ form.title }}
                            </option>
                        </select>
                    </div>
                    <div class="w-44">
                        <label class="admin-filter-label">Статус</label>
                        <select
                            v-model="filters.status"
                            @change="applyFilters"
                            class="admin-filter-select"
                        >
                            <option :value="undefined">Все</option>
                            <option value="unread">Непрочитанные</option>
                            <option value="read">Прочитанные</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>

            <!-- Контент (скроллится) -->
            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <!-- Десктопная версия -->
                    <div class="admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead>
                            <tr>
                                <th class="col-checkbox w-12">
                                    <input type="checkbox" v-model="allSelected" class="admin-checkbox">
                                </th>
                                <th class="col-status w-12">Статус</th>
                                <th class="col-form min-w-[180px]">Форма</th>
                                <th class="col-sender min-w-[180px]">Отправитель</th>
                                <th class="col-preview min-w-[300px]">Сообщение</th>
                                <th class="col-date w-40">Дата</th>
                                <th class="col-actions w-24 text-right">Действия</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr
                                v-for="submission in submissions.data"
                                :key="submission.id"
                                class="cursor-pointer"
                                :class="{
                                    'bg-blue-50/50': selectedSubmissions.includes(submission.id),
                                    'hover:bg-slate-50': !selectedSubmissions.includes(submission.id),
                                    'hover:bg-blue-100/50': selectedSubmissions.includes(submission.id)
                                }"
                            >
                                <!-- Чекбокс -->
                                <td class="col-checkbox" @click.stop>
                                    <input
                                        type="checkbox"
                                        :checked="selectedSubmissions.includes(submission.id)"
                                        @change="toggleSelect(submission.id)"
                                        class="admin-checkbox"
                                    />
                                </td>

                                <!-- Статус -->
                                <td class="col-status" @click="toggleSelect(submission.id)">
                                    <span
                                        v-if="!submission.read_at"
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Новое
                                    </span>
                                    <span
                                        v-else
                                        class="text-xs text-slate-400"
                                    >
                                        Прочитано
                                    </span>
                                </td>

                                <!-- Форма -->
                                <td class="col-form" @click="toggleSelect(submission.id)">
                                    <span
                                        class="font-medium text-[#3071a9] hover:underline cursor-pointer"
                                        @click.stop="openViewModal(submission.id)"
                                    >
                                        {{ submission.form.title }}
                                    </span>
                                </td>

                                <!-- Отправитель -->
                                <td class="col-sender" @click="toggleSelect(submission.id)">
                                    <div class="text-sm font-medium text-slate-700">
                                        {{ submission.display_name }}
                                    </div>
                                </td>

                                <!-- Предпросмотр -->
                                <td class="col-preview" @click="toggleSelect(submission.id)">
                                    <div class="text-sm text-slate-600 line-clamp-2">
                                        {{ getPreviewText(submission.data) }}
                                    </div>
                                </td>

                                <!-- Дата -->
                                <td class="col-date" @click="toggleSelect(submission.id)">
                                    <div class="text-sm text-slate-600">
                                        {{ formatDate(submission.created_at) }}
                                    </div>
                                </td>

                                <!-- Действия -->
                                <td class="col-actions text-right" @click.stop>
                                    <button
                                        @click="openDeleteModal(submission.id)"
                                        class="text-red-400 hover:text-red-600 transition-colors text-sm"
                                        title="Удалить"
                                    >
                                        🗑
                                    </button>
                                </td>
                            </tr>

                            <!-- Пустая строка, если нет данных -->
                            <tr v-if="submissions.data.length === 0">
                                <td colspan="7" style="text-align: center; padding: 40px 0; color: #94a3b8;">
                                    Нет обращений
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Пагинация -->
                    <div v-if="submissions.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">
                            Показано {{ submissions.from || 0 }} - {{ submissions.to || 0 }} из {{ submissions.total || 0 }}
                        </div>
                        <div class="admin-pagination-controls">
                            <button
                                @click="prevPage"
                                :disabled="submissions.current_page === 1"
                                class="admin-pagination-btn"
                            >
                                ← Назад
                            </button>
                            <span class="admin-pagination-current">
                                {{ submissions.current_page }} / {{ submissions.last_page }}
                            </span>
                            <button
                                @click="nextPage"
                                :disabled="submissions.current_page === submissions.last_page"
                                class="admin-pagination-btn"
                            >
                                Вперед →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка просмотра -->
        <div
            v-if="viewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            @click="viewModalOpen = false"
        >
            <div
                class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col"
                @click.stop
            >
                <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
                    <h2 class="text-xl font-semibold text-gray-800">
                        {{ viewingSubmission?.form?.title || 'Сообщение' }}
                    </h2>
                    <button
                        @click="viewModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 transition text-xl"
                    >
                        ×
                    </button>
                </div>

                <div class="flex-1 overflow-auto p-4">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm text-gray-500 border-b pb-2">
                            <span>От: {{ viewingSubmission?.display_name || 'Аноним' }}</span>
                            <span>{{ formatDate(viewingSubmission?.created_at) }}</span>
                        </div>
                        <div
                            v-if="viewingSubmission?.data"
                            class="space-y-2"
                        >
                            <div
                                v-for="(value, key) in viewingSubmission.data"
                                :key="key"
                                class="border-b border-gray-100 pb-2"
                            >
                                <div class="text-xs font-medium text-gray-500 uppercase">
                                    {{ key }}
                                </div>
                                <div class="text-sm text-gray-700 mt-0.5 whitespace-pre-wrap">
                                    {{ value || '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-4 border-t border-gray-200 flex-shrink-0">
                    <button
                        @click="viewModalOpen = false"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
                    >
                        Закрыть
                    </button>
                    <button
                        v-if="viewingSubmission && !viewingSubmission.read_at"
                        @click="markAsRead(viewingSubmission.id)"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                    >
                        Отметить как прочитанное
                    </button>
                    <button
                        @click="confirmDeleteFromModal(viewingSubmission?.id)"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition"
                    >
                        Удалить
                    </button>
                </div>
            </div>
        </div>

        <!-- Модалки подтверждения -->
        <ConfirmModal
            :is-open="deleteModalOpen"
            title="Удаление обращения"
            :message="deleteMessage"
            confirm-text="Удалить"
            type="danger"
            :loading="deleteLoading"
            @close="deleteModalOpen = false"
            @confirm="confirmDeleteHandler"
        />

        <ConfirmModal
            :is-open="markReadModalOpen"
            title="Отметить как прочитанное"
            :message="markReadMessage"
            confirm-text="Отметить"
            type="info"
            :loading="loading"
            @close="markReadModalOpen = false"
            @confirm="confirmMarkRead"
        />

        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useSubmissions } from '@/composables/useSubmissions';

const props = defineProps<{
    user: any;
    title?: string;
    submissions: any;
    forms: any[];
    filters?: any;
    unreadCount: number;
}>();

const {
    filters,
    selectedSubmissions,
    allSelected,
    notification,
    loading,
    deleteModalOpen,
    deleteLoading,
    deleteMessage,
    markReadModalOpen,
    markReadMessage,
    viewModalOpen,
    viewingSubmission,
    applyFilters,
    debounceSearch,
    resetFilters,
    prevPage,
    nextPage,
    toggleSelect,
    openDeleteModal,
    openDeleteModalForSelected,
    confirmDeleteHandler,
    openMarkReadModal,
    confirmMarkRead,
    openViewModal,
    openViewModalForSelected,
    formatDate,
    getPreviewText,
    markAsRead,
    confirmDeleteFromModal,
} = useSubmissions(props);
</script>
