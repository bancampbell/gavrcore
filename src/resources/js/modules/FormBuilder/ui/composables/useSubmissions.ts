import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { submissionApi } from '../../infrastructure/api/submission-api';
import type { SubmissionFilters, PaginatedData, Submission } from '../types';

export function useSubmissions(props: any) {
    const filters = ref<SubmissionFilters>({
        search: props.filters?.search || '',
        status: props.filters?.status || undefined,
        form_id: props.filters?.form_id || undefined,
    });
    const selectedSubmissions = ref<number[]>([]);
    const loading = ref(false);
    const deleteLoading = ref(false);
    const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' | 'info' });
    let notificationTimeout: any = null;

    const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
        notification.value = { show: true, message, type };
        notificationTimeout = setTimeout(() => notification.value.show = false, 5000);
    };

    const allSelected = computed({
        get: () => props.submissions?.data?.length > 0 && props.submissions.data.every((item: any) => selectedSubmissions.value.includes(item.id)),
        set: (value: boolean) => {
            selectedSubmissions.value = value ? props.submissions.data.map((item: any) => item.id) : [];
        },
    });

    const toggleSelect = (id: number) => {
        const idx = selectedSubmissions.value.indexOf(id);
        if (idx === -1) selectedSubmissions.value.push(id);
        else selectedSubmissions.value.splice(idx, 1);
    };

    let searchTimeout: any = null;
    const applyFilters = () => {
        const query: any = {};
        if (filters.value.search) query.search = filters.value.search;
        if (filters.value.status) query.status = filters.value.status;
        if (filters.value.form_id) query.form_id = filters.value.form_id;
        router.get('/admin/submissions', query, { preserveState: true, preserveScroll: true });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    };

    const resetFilters = () => {
        filters.value = { search: '', status: undefined, form_id: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (props.submissions?.current_page > 1) {
            router.get('/admin/submissions', { page: props.submissions.current_page - 1, ...filters.value }, { preserveState: true, preserveScroll: true });
        }
    };

    const nextPage = () => {
        if (props.submissions?.current_page < props.submissions?.last_page) {
            router.get('/admin/submissions', { page: props.submissions.current_page + 1, ...filters.value }, { preserveState: true, preserveScroll: true });
        }
    };

    const viewModalOpen = ref(false);
    const viewingSubmission = ref<any>(null);

    const openViewModal = async (id: number) => {
        loading.value = true;
        try {
            const { data } = await submissionApi.showSubmission(id);
            if (data.submission) {
                viewingSubmission.value = data.submission;
                viewModalOpen.value = true;
                if (!data.submission.read_at) applyFilters();
            }
        } catch {
            showNotification('Ошибка загрузки сообщения', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openViewModalForSelected = () => {
        if (selectedSubmissions.value.length !== 1) {
            showNotification('Выберите одно обращение для просмотра', 'info');
            return;
        }
        openViewModal(selectedSubmissions.value[0]);
    };

    const markAsRead = async (id: number) => {
        loading.value = true;
        try {
            await submissionApi.markAsRead([id]);
            showNotification('Отмечено как прочитанное', 'success');
            viewModalOpen.value = false;
            applyFilters();
        } catch {
            showNotification('Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const markReadModalOpen = ref(false);
    const markReadMessage = ref('');

    const openMarkReadModal = () => {
        if (!selectedSubmissions.value.length) return;
        markReadMessage.value = `Отметить как прочитанное ${selectedSubmissions.value.length} обращений?`;
        markReadModalOpen.value = true;
    };

    const confirmMarkRead = async () => {
        if (!selectedSubmissions.value.length) return;
        loading.value = true;
        try {
            const { data } = await submissionApi.markAsRead(selectedSubmissions.value);
            showNotification(data.message || 'Отмечено как прочитанное', 'success');
            selectedSubmissions.value = [];
            markReadModalOpen.value = false;
            applyFilters();
        } catch {
            showNotification('Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const deleteModalOpen = ref(false);
    const deleteMessage = ref('');
    const deleteId = ref<number | null>(null);

    const openDeleteModal = (id: number) => {
        deleteId.value = id;
        deleteMessage.value = 'Вы уверены, что хотите удалить это обращение?';
        deleteModalOpen.value = true;
    };

    const openDeleteModalForSelected = () => {
        if (!selectedSubmissions.value.length) return;
        deleteMessage.value = `Вы уверены, что хотите удалить ${selectedSubmissions.value.length} обращений?`;
        deleteId.value = null;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        deleteLoading.value = true;
        try {
            if (deleteId.value !== null) {
                await submissionApi.deleteSubmission(deleteId.value);
            } else {
                await submissionApi.deleteBulk(selectedSubmissions.value);
            }
            showNotification('Удалено', 'success');
            selectedSubmissions.value = [];
            deleteModalOpen.value = false;
            deleteId.value = null;
            viewModalOpen.value = false;
            applyFilters();
        } catch {
            showNotification('Ошибка удаления', 'error');
        } finally {
            deleteLoading.value = false;
        }
    };

    const confirmDeleteFromModal = (id: number | undefined) => {
        if (!id) return;
        deleteId.value = id;
        deleteMessage.value = 'Вы уверены, что хотите удалить это обращение?';
        deleteModalOpen.value = true;
        viewModalOpen.value = false;
    };

    const formatDate = (date: string | null) => {
        if (!date) return '—';
        return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    };

    const getPreviewText = (data: any) => {
        if (!data) return '—';
        const textFields = ['message','Message','MESSAGE','text','Text','TEXT','comment','Comment','COMMENT','сообщение','Сообщение'];
        const dataObj = typeof data === 'string' ? JSON.parse(data) : data;
        for (const field of textFields) {
            if (dataObj[field] && typeof dataObj[field] === 'string') {
                const text = dataObj[field].substring(0, 150);
                return text.length < dataObj[field].length ? text + '...' : text;
            }
        }
        for (const key in dataObj) {
            if (typeof dataObj[key] === 'string' && dataObj[key].length > 0) {
                const text = dataObj[key].substring(0, 150);
                return text.length < dataObj[key].length ? text + '...' : text;
            }
        }
        return JSON.stringify(dataObj).substring(0, 100);
    };

    watch(() => props.filters, (newFilters) => {
        if (newFilters) {
            filters.value.search = newFilters.search || '';
            filters.value.status = newFilters.status || undefined;
            filters.value.form_id = newFilters.form_id || undefined;
        }
    }, { immediate: true });

    return {
        filters, selectedSubmissions, allSelected, notification, loading, deleteLoading,
        deleteModalOpen, deleteMessage, markReadModalOpen, markReadMessage,
        viewModalOpen, viewingSubmission, applyFilters, debounceSearch, resetFilters,
        prevPage, nextPage, toggleSelect, openDeleteModal, openDeleteModalForSelected,
        confirmDeleteHandler, showNotification, openMarkReadModal, confirmMarkRead,
        openViewModal, openViewModalForSelected, formatDate, getPreviewText, markAsRead,
        confirmDeleteFromModal,
    };
}