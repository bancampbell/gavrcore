<template>
    <AdminLayout :user="user">
        <Head><title>{{ title }}</title></Head>
        <div class="flex flex-col h-full w-full">
            <div class="admin-page-actions flex-shrink-0 w-full">
                <h1 class="admin-page-title">Формы</h1>
                <div class="flex flex-wrap gap-2.5">
                    <Link href="/admin/forms/create" class="admin-btn admin-btn-primary no-style">+ Создать форму</Link>
                    <template v-if="selectedForms.length > 0">
                        <button @click="editSelected" :disabled="selectedForms.length !== 1" class="admin-btn admin-btn-secondary">Редактировать</button>
                        <button @click="handlePublish" class="admin-btn admin-btn-secondary">Опубликовать</button>
                        <button @click="handleUnpublish" class="admin-btn admin-btn-secondary">Снять с публикации</button>
                        <button @click="openDeleteModal" class="admin-btn admin-btn-danger">Удалить</button>
                    </template>
                </div>
                <div class="admin-filters-inline">
                    <div class="admin-filter-group">
                        <label class="admin-filter-label">Поиск</label>
                        <input type="text" v-model="filters.search" @input="debounceSearch" placeholder="Введите название..." class="admin-filter-input" />
                    </div>
                    <div class="w-40">
                        <label class="admin-filter-label">Статус</label>
                        <select v-model="filters.status" @change="applyFilters" class="admin-filter-select">
                            <option :value="undefined">Все</option>
                            <option :value="1">Опубликовано</option>
                            <option :value="0">Черновик</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="admin-filter-reset">Очистить</button>
                </div>
            </div>
            <div class="admin-page-content">
                <div class="admin-page-card w-full">
                    <div class="admin-table-scroll">
                        <table class="admin-table-fixed">
                            <thead><tr><th class="col-checkbox"><input type="checkbox" v-model="allSelected" class="admin-checkbox"></th><th class="col-title">Название</th><th class="col-alias">Алиас</th><th class="col-status">Статус</th><th class="col-items">Полей</th><th class="col-created">Дата создания</th><th class="col-id">ID</th></tr></thead>
                            <tbody>
                            <tr v-for="form in forms.data" :key="form.id" class="cursor-pointer" :class="{ 'bg-blue-50/50': selectedForms.includes(form.id) }">
                                <td class="col-checkbox" @click.stop><input type="checkbox" :checked="selectedForms.includes(form.id)" @change="toggleSelect(form.id)" class="admin-checkbox" /></td>
                                <td class="col-title" @click="toggleSelect(form.id)"><Link :href="`/admin/forms/${form.id}/edit`" class="title-text">{{ form.title }}</Link></td>
                                <td class="col-alias" @click="toggleSelect(form.id)">{{ form.alias }}</td>
                                <td class="col-status" @click="toggleSelect(form.id)"><span class="status-badge" :class="form.status ? 'status-published' : 'status-draft'">{{ form.status ? 'Опубликовано' : 'Черновик' }}</span></td>
                                <td class="col-items" @click="toggleSelect(form.id)"><span class="stat-badge stat-badge-blue">{{ form.fields ? form.fields.length : 0 }}</span></td>
                                <td class="col-created" @click="toggleSelect(form.id)">{{ formatDate(form.created_at) }}</td>
                                <td class="col-id" @click="toggleSelect(form.id)">{{ form.id }}</td>
                            </tr>
                            <tr v-if="forms.data?.length === 0"><td colspan="7" style="text-align: center; padding: 40px 0; color: #94a3b8;">Нет форм<p style="font-size: 12px; margin-top: 4px;">Создайте первую форму</p></td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="forms.data?.length > 0" class="admin-pagination">
                        <div class="admin-pagination-info">Показано {{ forms.from || 0 }} - {{ forms.to || 0 }} из {{ forms.total || 0 }}</div>
                        <div class="admin-pagination-controls">
                            <button @click="prevPage" :disabled="forms.current_page === 1" class="admin-pagination-btn">← Назад</button>
                            <span class="admin-pagination-current">{{ forms.current_page }} / {{ forms.last_page }}</span>
                            <button @click="nextPage" :disabled="forms.current_page === forms.last_page" class="admin-pagination-btn">Вперед →</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmModal :is-open="deleteModalOpen" title="Удаление формы" :message="deleteMessage" confirm-text="Удалить" type="danger" :loading="deleteLoading" @close="closeDeleteModal" @confirm="confirmDelete" />
        <Toast :show="notification.show" :message="notification.message" :type="notification.type" />
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ConfirmModal from '@/components/shared/ConfirmModal.vue';
import Toast from '@/components/shared/Toast.vue';
import { useForms } from './composables/useForms';

interface Form { id: number; title: string; alias: string; description: string | null; status: boolean; fields: any[]; submissions_count: number; created_at: string; updated_at: string; }
interface FormsData { data: Form[]; current_page: number; last_page: number; from: number | null; to: number | null; total: number; }

const props = defineProps<{ user: any; title?: string; forms: FormsData; filters?: { search?: string; status?: boolean; }; }>();

const { selectedForms, filters, loading: bulkLoading, notification, allSelected, toggleSelect, applyFilters, debounceSearch, resetFilters, prevPage, nextPage, handlePublish, handleUnpublish, confirmDelete: deleteForms, formatDate, showNotification } = useForms(props);

const deleteModalOpen = ref(false);
const deleteLoading = ref(false);
const deleteMessage = ref('');
const itemsToDelete = ref<number[]>([]);

const editSelected = () => { if (selectedForms.value.length === 1) router.visit(`/admin/forms/${selectedForms.value[0]}/edit`); };

const openDeleteModal = () => {
    if (selectedForms.value.length === 0) return;
    const count = selectedForms.value.length;
    deleteMessage.value = count === 1 ? 'Вы уверены, что хотите удалить выбранную форму? Это действие нельзя отменить.' : `Вы уверены, что хотите удалить ${count} форм? Это действие нельзя отменить.`;
    itemsToDelete.value = [...selectedForms.value]; deleteModalOpen.value = true;
};
const closeDeleteModal = () => { deleteModalOpen.value = false; deleteLoading.value = false; itemsToDelete.value = []; };
const confirmDelete = async () => {
    if (itemsToDelete.value.length === 0) return;
    deleteLoading.value = true;
    try { await deleteForms(itemsToDelete.value); selectedForms.value = []; closeDeleteModal(); }
    catch (error: any) { showNotification(error.response?.data?.message || 'Ошибка при удалении', 'error'); deleteLoading.value = false; }
};

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    if (message) { showNotification(decodeURIComponent(message), 'success'); const url = new URL(window.location.href); url.searchParams.delete('message'); window.history.replaceState({}, '', url.toString()); }
});
</script>