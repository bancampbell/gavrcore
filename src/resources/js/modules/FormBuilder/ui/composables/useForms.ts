import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { formApi } from '../../infrastructure/api/form-api';
import type { Form, FormFilters, PaginatedData } from '../types';

export function useForms(props: { forms: PaginatedData<Form>; filters?: FormFilters }) {
    const selectedForms = ref<number[]>([]);
    const filters = ref<FormFilters>({
        search: props.filters?.search || '',
        status: props.filters?.status as any,
    });
    const loading = ref(false);
    const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
    let searchTimeout: any = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => notification.value.show = false, 5000);
    };

    const applyFilters = () => {
        router.get('/admin/forms', {
            search: filters.value.search,
            status: filters.value.status,
        }, { preserveState: true, preserveScroll: true });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    };

    const resetFilters = () => {
        filters.value = { search: '', status: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (props.forms.current_page > 1) {
            router.visit(`/admin/forms?page=${props.forms.current_page - 1}`);
        }
    };

    const nextPage = () => {
        if (props.forms.current_page < props.forms.last_page) {
            router.visit(`/admin/forms?page=${props.forms.current_page + 1}`);
        }
    };

    const toggleSelect = (id: number) => {
        const idx = selectedForms.value.indexOf(id);
        if (idx === -1) selectedForms.value.push(id);
        else selectedForms.value.splice(idx, 1);
    };

    const allSelected = computed({
        get: () => props.forms.data?.length > 0 && selectedForms.value.length === props.forms.data.length,
        set: (val: boolean) => {
            selectedForms.value = val ? props.forms.data.map(f => f.id) : [];
        }
    });

    const handlePublish = async () => {
        if (!selectedForms.value.length) return;
        loading.value = true;
        try {
            for (const id of selectedForms.value) await formApi.updateFormStatus(id, true);
            showNotification(`${selectedForms.value.length} форм опубликовано`, 'success');
            selectedForms.value = [];
            applyFilters();
        } catch (e: any) {
            showNotification(e.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const handleUnpublish = async () => {
        if (!selectedForms.value.length) return;
        loading.value = true;
        try {
            for (const id of selectedForms.value) await formApi.updateFormStatus(id, false);
            showNotification(`${selectedForms.value.length} форм снято с публикации`, 'success');
            selectedForms.value = [];
            applyFilters();
        } catch (e: any) {
            showNotification(e.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const confirmDelete = async (ids: number[]) => {
        loading.value = true;
        try {
            for (const id of ids) await formApi.deleteForm(id);
            showNotification(`${ids.length} форм удалено`, 'success');
            selectedForms.value = [];
            applyFilters();
        } catch (e: any) {
            showNotification(e.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            loading.value = false;
        }
    };

    const formatDate = (date: string | null) => {
        if (!date) return '—';
        return new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    return {
        selectedForms, filters, loading, notification,
        allSelected, toggleSelect, applyFilters, debounceSearch,
        resetFilters, prevPage, nextPage, handlePublish, handleUnpublish,
        confirmDelete, formatDate, showNotification,
    };
}