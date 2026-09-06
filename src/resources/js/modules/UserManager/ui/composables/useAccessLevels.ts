import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { accessLevelsApi } from '../../infrastructure/api/user-api';
import { NOTIFICATION_TIMEOUT_MS, SEARCH_DEBOUNCE_MS, DEFAULT_PER_PAGE } from '../constants';
import type { AccessLevel } from '../types';

export function useAccessLevels(props: { accessLevels: AccessLevel[] }) {
    // Фильтры (клиентские)
    const filters = ref({
        search: '',
        status: undefined as boolean | undefined,
    });

    // Выбранные элементы
    const selectedLevels = ref<number[]>([]);

    // Пагинация (клиентская)
    const perPage = ref(DEFAULT_PER_PAGE);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
        total: 0,
    });

    // Отфильтрованные уровни
    const filteredLevels = computed(() => {
        let levels = [...props.accessLevels];

        if (filters.value.search) {
            const search = filters.value.search.toLowerCase();
            levels = levels.filter(l =>
                l.title.toLowerCase().includes(search) ||
                l.alias.toLowerCase().includes(search)
            );
        }

        if (filters.value.status !== undefined) {
            levels = levels.filter(l => l.status === filters.value.status);
        }

        return levels;
    });

    const updatePagination = () => {
        pagination.value.total = filteredLevels.value.length;
        pagination.value.last_page = Math.ceil(pagination.value.total / perPage.value) || 1;
        pagination.value.from = (pagination.value.current_page - 1) * perPage.value + 1;
        pagination.value.to = Math.min(pagination.value.current_page * perPage.value, pagination.value.total);
    };

    const paginatedLevels = computed(() => {
        const start = (pagination.value.current_page - 1) * perPage.value;
        const end = start + perPage.value;
        return filteredLevels.value.slice(start, end);
    });

    // Выбрать все
    const allSelected = computed({
        get: () => {
            if (paginatedLevels.value.length === 0) return false;
            return selectedLevels.value.length === paginatedLevels.value.length;
        },
        set: (val: boolean) => {
            if (val) {
                selectedLevels.value = paginatedLevels.value.map(l => l.id);
            } else {
                selectedLevels.value = [];
            }
        },
    });

    const notification = ref({ show: false, message: '', type: 'success' });
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const itemToDelete = ref<AccessLevel | null>(null);
    const deleteMessage = ref('');

    const bulkDeleteModalOpen = ref(false);
    const bulkDeleteLoading = ref(false);
    const bulkDeleteMessage = ref('');

    const toggleStatusModalOpen = ref(false);
    const toggleStatusMessage = ref('');
    const toggleStatusData = ref<{ id: number; status: boolean } | null>(null);

    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

    const showNotification = (message: string, type: string = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => { notification.value.show = false; }, NOTIFICATION_TIMEOUT_MS);
    };

    const toggleSelect = (id: number) => {
        const index = selectedLevels.value.indexOf(id);
        if (index === -1) {
            selectedLevels.value.push(id);
        } else {
            selectedLevels.value.splice(index, 1);
        }
    };

    const applyFilters = () => {
        pagination.value.current_page = 1;
        updatePagination();
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, SEARCH_DEBOUNCE_MS);
    };

    const resetFilters = () => {
        filters.value = { search: '', status: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (pagination.value.current_page > 1) {
            pagination.value.current_page--;
            updatePagination();
        }
    };

    const nextPage = () => {
        if (pagination.value.current_page < pagination.value.last_page) {
            pagination.value.current_page++;
            updatePagination();
        }
    };

    // Перезагружаем props с сервера после мутаций
    const reloadLevels = () => {
        router.reload({ only: ['accessLevels'] });
    };

    const deleteLevel = (level: AccessLevel) => {
        itemToDelete.value = level;
        deleteMessage.value = `Удалить уровень доступа "${level.title}"?`;
        deleteModalOpen.value = true;
    };

    const confirmDelete = async () => {
        if (!itemToDelete.value) return;
        deleteLoading.value = true;
        try {
            await accessLevelsApi.delete(itemToDelete.value.id);
            deleteModalOpen.value = false;
            reloadLevels();
            showNotification('Уровень доступа удалён', 'success');
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            deleteLoading.value = false;
        }
    };

    const openBulkDeleteModal = () => {
        if (selectedLevels.value.length === 0) return;

        if (selectedLevels.value.length === 1) {
            const level = filteredLevels.value.find(l => l.id === selectedLevels.value[0]);
            if (level) {
                deleteLevel(level);
            }
        } else {
            bulkDeleteMessage.value = `Вы уверены, что хотите удалить ${selectedLevels.value.length} уровень(ей) доступа?`;
            bulkDeleteModalOpen.value = true;
        }
    };

    const confirmBulkDelete = async () => {
        bulkDeleteLoading.value = true;
        try {
            for (const id of selectedLevels.value) {
                await accessLevelsApi.delete(id);
            }
            const count = selectedLevels.value.length;
            selectedLevels.value = [];
            bulkDeleteModalOpen.value = false;
            reloadLevels();
            showNotification(`${count} уровней доступа удалено`, 'success');
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            bulkDeleteLoading.value = false;
        }
    };

    // Переключение статуса (одиночное) — целевой статус вычисляется здесь,
    // сервер применяет его как есть, без тогла.
    const toggleStatus = (level: AccessLevel) => {
        const newStatus = !level.status;
        toggleStatusData.value = { id: level.id, status: newStatus };
        toggleStatusMessage.value = `Вы уверены, что хотите ${newStatus ? 'активировать' : 'деактивировать'} уровень "${level.title}"?`;
        toggleStatusModalOpen.value = true;
    };

    const confirmToggleStatus = async () => {
        if (!toggleStatusData.value) return;
        loading.value = true;
        try {
            const targetStatus = toggleStatusData.value.status;

            if (toggleStatusData.value.id === 0) {
                // Массовая операция — каждому выбранному id ставим один и тот же целевой статус
                const ids = [...selectedLevels.value];
                for (const id of ids) {
                    await accessLevelsApi.updateStatus(id, targetStatus);
                }
                selectedLevels.value = [];
                showNotification(`${ids.length} уровней доступа ${targetStatus ? 'активировано' : 'деактивировано'}`, 'success');
            } else {
                // Одиночная операция
                await accessLevelsApi.updateStatus(toggleStatusData.value.id, targetStatus);
                showNotification(`Уровень доступа ${targetStatus ? 'активирован' : 'деактивирован'}`, 'success');
            }
            toggleStatusModalOpen.value = false;
            reloadLevels();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
            toggleStatusData.value = null;
        }
    };

    // Массовые операции со статусом
    const bulkActivate = () => {
        if (selectedLevels.value.length === 0) return;
        bulkToggleStatus(true);
    };

    const bulkDeactivate = () => {
        if (selectedLevels.value.length === 0) return;
        bulkToggleStatus(false);
    };

    const bulkToggleStatus = (status: boolean) => {
        const action = status ? 'активировать' : 'деактивировать';
        toggleStatusMessage.value = `Вы уверены, что хотите ${action} ${selectedLevels.value.length} уровень(ей) доступа?`;
        toggleStatusData.value = { id: 0, status };
        toggleStatusModalOpen.value = true;
    };

    return {
        filters,
        selectedLevels,
        allSelected,
        filteredLevels,
        paginatedLevels,
        pagination,
        notification,
        loading,
        deleteModalOpen,
        deleteLoading,
        deleteMessage,
        bulkDeleteModalOpen,
        bulkDeleteLoading,
        bulkDeleteMessage,
        toggleStatusModalOpen,
        toggleStatusMessage,
        applyFilters,
        debounceSearch,
        resetFilters,
        prevPage,
        nextPage,
        toggleSelect,
        deleteLevel,
        confirmDelete,
        openBulkDeleteModal,
        confirmBulkDelete,
        toggleStatus,
        confirmToggleStatus,
        bulkActivate,
        bulkDeactivate,
        updatePagination,
        showNotification,
    };
}
