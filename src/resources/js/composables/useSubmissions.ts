import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

interface Filters {
    search?: string;
    status?: string;
    form_id?: number;
}

export function useSubmissions(props: any) {
    // ===== СОСТОЯНИЯ =====
    const filters = ref<Filters>({
        search: props.filters?.search || '',
        status: props.filters?.status || undefined,
        form_id: props.filters?.form_id || undefined,
    });

    const selectedSubmissions = ref<number[]>([]);
    const loading = ref(false);
    const deleteLoading = ref(false);

    // ===== УВЕДОМЛЕНИЯ =====
    const notification = ref({
        show: false,
        message: '',
        type: 'success' as 'success' | 'error' | 'info',
    });

    let notificationTimeout: any = null;

    const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'success') => {
        if (notificationTimeout) {
            clearTimeout(notificationTimeout);
        }
        notification.value = { show: true, message, type };
        notificationTimeout = setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    };

    // ===== ВЫБОР ВСЕХ =====
    const allSelected = computed({
        get: () => {
            if (!props.submissions?.data?.length) return false;
            return props.submissions.data.every((item: any) =>
                selectedSubmissions.value.includes(item.id)
            );
        },
        set: (value: boolean) => {
            if (value) {
                selectedSubmissions.value = props.submissions.data.map((item: any) => item.id);
            } else {
                selectedSubmissions.value = [];
            }
        },
    });

    const toggleAll = () => {
        if (allSelected.value) {
            selectedSubmissions.value = [];
        } else {
            const allIds = props.submissions?.data?.map((item: any) => item.id) || [];
            selectedSubmissions.value = allIds;
        }
    };

    const toggleSelect = (id: number) => {
        const index = selectedSubmissions.value.indexOf(id);
        if (index === -1) {
            selectedSubmissions.value.push(id);
        } else {
            selectedSubmissions.value.splice(index, 1);
        }
    };

    // ===== ФИЛЬТРЫ =====
    let searchTimeout: any = null;

    const applyFilters = () => {
        const query: any = {};
        if (filters.value.search) query.search = filters.value.search;
        if (filters.value.status) query.status = filters.value.status;
        if (filters.value.form_id) query.form_id = filters.value.form_id;

        router.get('/admin/submissions', query, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 500);
    };

    const resetFilters = () => {
        filters.value = {
            search: '',
            status: undefined,
            form_id: undefined,
        };
        applyFilters();
    };

    // ===== ПАГИНАЦИЯ =====
    const prevPage = () => {
        if (props.submissions?.current_page > 1) {
            router.get('/admin/submissions', {
                page: props.submissions.current_page - 1,
                ...filters.value,
            }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const nextPage = () => {
        if (props.submissions?.current_page < props.submissions?.last_page) {
            router.get('/admin/submissions', {
                page: props.submissions.current_page + 1,
                ...filters.value,
            }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    // ===== ОТКРЫТИЕ МОДАЛКИ ПРОСМОТРА =====
    const viewModalOpen = ref(false);
    const viewingSubmission = ref<any>(null);

    const openViewModal = async (id: number) => {
        loading.value = true;
        try {
            const response = await fetch(`/admin/submissions/${id}`);
            const data = await response.json();

            if (data.submission) {
                viewingSubmission.value = data.submission;
                viewModalOpen.value = true;

                // Обновляем список, если было непрочитанное
                if (!data.submission.read_at) {
                    applyFilters();
                }
            }
        } catch (error) {
            showNotification('Ошибка загрузки сообщения', 'error');
        } finally {
            loading.value = false;
        }
    };

    // ===== ОТКРЫТИЕ МОДАЛКИ ДЛЯ ВЫБРАННОГО =====
    const openViewModalForSelected = () => {
        if (selectedSubmissions.value.length === 0) {
            showNotification('Выберите обращение для просмотра', 'info');
            return;
        }
        if (selectedSubmissions.value.length !== 1) {
            showNotification('Выберите одно обращение для просмотра', 'info');
            return;
        }
        openViewModal(selectedSubmissions.value[0]);
    };

    // ===== ОТМЕТКА КАК ПРОЧИТАННОЕ (одиночное) =====
    const markAsRead = async (id: number) => {
        loading.value = true;
        try {
            const response = await fetch('/admin/submissions/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ ids: [id] }),
            });

            const data = await response.json();
            showNotification(data.message || 'Отмечено как прочитанное', 'success');
            viewModalOpen.value = false;
            applyFilters();
        } catch (error) {
            showNotification('Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    // ===== МАССОВАЯ ОТМЕТКА КАК ПРОЧИТАННОЕ =====
    const markReadModalOpen = ref(false);
    const markReadMessage = ref('');

    const openMarkReadModal = () => {
        if (selectedSubmissions.value.length === 0) return;

        const count = selectedSubmissions.value.length;
        markReadMessage.value = `Отметить как прочитанное ${count} обращений?`;
        markReadModalOpen.value = true;
    };

    const confirmMarkRead = async () => {
        if (selectedSubmissions.value.length === 0) return;

        loading.value = true;
        try {
            const response = await fetch('/admin/submissions/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ ids: selectedSubmissions.value }),
            });

            const data = await response.json();
            showNotification(data.message || `Отмечено как прочитанное: ${data.count}`, 'success');
            selectedSubmissions.value = [];
            markReadModalOpen.value = false;
            applyFilters();
        } catch (error) {
            showNotification('Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    // ===== УДАЛЕНИЕ =====
    const deleteModalOpen = ref(false);
    const deleteMessage = ref('');
    const deleteId = ref<number | null>(null);

    const openDeleteModal = (id: number) => {
        deleteId.value = id;
        deleteMessage.value = 'Вы уверены, что хотите удалить это обращение?';
        deleteModalOpen.value = true;
    };

    const openDeleteModalForSelected = () => {
        if (selectedSubmissions.value.length === 0) return;

        const count = selectedSubmissions.value.length;
        deleteMessage.value = `Вы уверены, что хотите удалить ${count} обращений?`;
        deleteId.value = null;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        deleteLoading.value = true;
        try {
            let url = '/admin/submissions/';
            let method = 'DELETE';
            let body: any = {};

            if (deleteId.value !== null) {
                // Удаление одного
                url += deleteId.value;
                body = {};
            } else {
                // Массовое удаление
                url = '/admin/submissions/destroy-bulk';
                body = { ids: selectedSubmissions.value };
            }

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(body),
            });

            const data = await response.json();

            if (data.message) {
                showNotification(data.message, 'success');
            }

            selectedSubmissions.value = [];
            deleteModalOpen.value = false;
            deleteId.value = null;
            viewModalOpen.value = false;
            applyFilters();
        } catch (error) {
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

    // ===== ФОРМАТИРОВАНИЕ ДАТЫ =====
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

    // ===== ПРЕДПРОСМОТР СООБЩЕНИЯ =====
    const getPreviewText = (data: any) => {
        if (!data) return '—';

        // Ищем текстовые поля для предпросмотра
        const textFields = ['message', 'Message', 'MESSAGE', 'text', 'Text', 'TEXT', 'comment', 'Comment', 'COMMENT', 'сообщение', 'Сообщение'];
        const dataObj = typeof data === 'string' ? JSON.parse(data) : data;

        for (const field of textFields) {
            if (dataObj[field] && typeof dataObj[field] === 'string') {
                const text = dataObj[field].substring(0, 150);
                return text.length < dataObj[field].length ? text + '...' : text;
            }
        }

        // Если нет текстовых полей - показываем первое строковое значение
        for (const key in dataObj) {
            if (typeof dataObj[key] === 'string' && dataObj[key].length > 0) {
                const text = dataObj[key].substring(0, 150);
                return text.length < dataObj[key].length ? text + '...' : text;
            }
        }

        return JSON.stringify(dataObj).substring(0, 100);
    };

    // ===== WATCH для обновления фильтров =====
    watch(
        () => props.filters,
        (newFilters) => {
            if (newFilters) {
                filters.value.search = newFilters.search || '';
                filters.value.status = newFilters.status || undefined;
                filters.value.form_id = newFilters.form_id || undefined;
            }
        },
        { immediate: true }
    );

    return {
        filters,
        selectedSubmissions,
        allSelected,
        notification,
        loading,
        deleteLoading,
        deleteModalOpen,
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
        toggleAll,
        openDeleteModal,
        openDeleteModalForSelected,
        confirmDeleteHandler,
        showNotification,
        openMarkReadModal,
        confirmMarkRead,
        openViewModal,
        openViewModalForSelected,
        formatDate,
        getPreviewText,
        markAsRead,
        confirmDeleteFromModal,
    };
}
