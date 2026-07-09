import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { materialsApi } from '../api/materials';
import type { MaterialsData, MaterialFilters, Notification } from '../types';

interface UseMaterialsProps {
    materials: MaterialsData;
    filters?: MaterialFilters;
    perPage?: number;
}

export function useMaterials(props: UseMaterialsProps) {
    // Преобразуем числовые фильтры из строк в числа
    const filters = ref<MaterialFilters>({
        search: props.filters?.search || '',
        state: props.filters?.state || '',
        category_id: props.filters?.category_id ? Number(props.filters.category_id) : null,
        author: props.filters?.author ? Number(props.filters.author) : null
    });

    const perPage = ref<number>(props.perPage || 10);
    const selectedMaterials = ref<number[]>([]);
    const allSelected = ref<boolean>(false);
    const notification = ref<Notification>({ show: false, message: '', type: 'success' });
    const modalOpen = ref<boolean>(false);
    const modalTitle = ref<string>('');
    const modalMessage = ref<string>('');
    const modalConfirmText = ref<string>('');
    const modalLoading = ref<boolean>(false);
    let pendingAction: 'delete' | 'empty' | 'restore' | null = null;

    let notificationTimeout: number | null = null;
    let searchTimeout: number | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
        notification.value = { show: true, message, type };
        notificationTimeout = window.setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    };

    const formatDate = (date: string): string => {
        if (!date) return '';
        const d = new Date(date);
        return `${d.getDate().toString().padStart(2, '0')}.${(d.getMonth() + 1).toString().padStart(2, '0')}.${d.getFullYear().toString().slice(-2)} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
    };

    const selectAll = () => {
        if (allSelected.value) {
            selectedMaterials.value = props.materials.data.map(m => m.id);
        } else {
            selectedMaterials.value = [];
        }
    };

    watch(selectedMaterials, (val) => {
        allSelected.value = val.length === props.materials.data?.length;
    });

    const applyFilters = () => {
        router.get('/admin/materials', {
            search: filters.value.search,
            state: filters.value.state,
            category_id: filters.value.category_id ?? null,
            author: filters.value.author ?? null,
            per_page: perPage.value
        }, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = window.setTimeout(() => {
            applyFilters();
        }, 300);
    };

    const resetFilters = () => {
        filters.value = {
            search: '',
            state: '',
            category_id: null,
            author: null
        };
        applyFilters();
    };

    const changePerPage = () => {
        applyFilters();
    };

    const prevPage = () => {
        if (props.materials.current_page > 1) {
            router.get(`/admin/materials?page=${props.materials.current_page - 1}`, {
                search: filters.value.search,
                state: filters.value.state,
                category_id: filters.value.category_id ?? null,
                author: filters.value.author ?? null,
                per_page: perPage.value
            });
        }
    };

    const nextPage = () => {
        if (props.materials.current_page < props.materials.last_page) {
            router.get(`/admin/materials?page=${props.materials.current_page + 1}`, {
                search: filters.value.search,
                state: filters.value.state,
                category_id: filters.value.category_id ?? null,
                author: filters.value.author ?? null,
                per_page: perPage.value
            });
        }
    };

    const moveToTrash = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialsApi.moveToTrash(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при перемещении в корзину', 'error');
        }
    };

    // ========================================
    // ВОССТАНОВЛЕНИЕ ИЗ КОРЗИНЫ
    // ========================================
    const restoreSelected = () => {
        if (selectedMaterials.value.length === 0) {
            showNotification('Выберите материалы для восстановления', 'error');
            return;
        }

        const count = selectedMaterials.value.length;
        modalTitle.value = 'Восстановление материалов';
        modalMessage.value = count === 1
            ? 'Вы уверены, что хотите восстановить выбранный материал?'
            : `Вы уверены, что хотите восстановить ${count} материалов?`;
        modalConfirmText.value = 'Восстановить';
        pendingAction = 'restore';
        modalOpen.value = true;
    };

    const publishSelected = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialsApi.publish(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при публикации', 'error');
        }
    };

    const unpublishSelected = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialsApi.unpublish(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при снятии с публикации', 'error');
        }
    };

    const toggleHomepage = async (material: any) => {
        try {
            const newValue = !material.show_on_homepage;
            const response = await materialsApi.toggleHomepage(material.id, newValue);
            showNotification(response.message, 'success');

            if (newValue) {
                props.materials.data.forEach(m => {
                    if (m.id !== material.id) {
                        m.show_on_homepage = false;
                    }
                });
            }

            const index = props.materials.data.findIndex(m => m.id === material.id);
            if (index !== -1) {
                props.materials.data[index].show_on_homepage = newValue;
            }
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при обновлении статуса "На главной"', 'error');
        }
    };

    const editSelected = () => {
        if (selectedMaterials.value.length === 0) {
            showNotification('Выберите материал для редактирования', 'error');
        } else if (selectedMaterials.value.length === 1) {
            router.visit(`/admin/materials/${selectedMaterials.value[0]}/edit`);
        } else {
            showNotification('Выберите только один материал для редактирования', 'error');
        }
    };

    const openDeleteModal = () => {
        const count = selectedMaterials.value.length;
        modalTitle.value = 'Удаление материалов';
        modalMessage.value = count === 1
            ? 'Вы уверены, что хотите удалить выбранный материал навсегда? Это действие нельзя отменить.'
            : `Вы уверены, что хотите удалить ${count} материалов навсегда? Это действие нельзя отменить.`;
        modalConfirmText.value = 'Удалить навсегда';
        pendingAction = 'delete';
        modalOpen.value = true;
    };

    const openEmptyTrashModal = () => {
        modalTitle.value = 'Очистка корзины';
        modalMessage.value = 'Вы уверены, что хотите очистить корзину? Все материалы будут удалены навсегда.';
        modalConfirmText.value = 'Очистить корзину';
        pendingAction = 'empty';
        modalOpen.value = true;
    };

    // ========================================
    // ПОДТВЕРЖДЕНИЕ ДЕЙСТВИЯ
    // ========================================
    const confirmAction = async () => {
        modalLoading.value = true;

        try {
            let response;
            if (pendingAction === 'restore') {
                response = await materialsApi.restore(selectedMaterials.value);
            } else if (pendingAction === 'delete') {
                response = await materialsApi.forceDelete(selectedMaterials.value);
            } else if (pendingAction === 'empty') {
                response = await materialsApi.emptyTrash();
            } else {
                modalLoading.value = false;
                return;
            }

            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            modalOpen.value = false;
            pendingAction = null;

            // Перезагружаем страницу, чтобы обновить список
            setTimeout(() => {
                router.reload();
            }, 1500);
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при выполнении операции', 'error');
            modalLoading.value = false;
            pendingAction = null;
        } finally {
            modalLoading.value = false;
        }
    };

    return {
        filters,
        perPage,
        selectedMaterials,
        allSelected,
        notification,
        modalOpen,
        modalTitle,
        modalMessage,
        modalConfirmText,
        modalLoading,
        formatDate,
        selectAll,
        applyFilters,
        debounceSearch,
        resetFilters,
        changePerPage,
        prevPage,
        nextPage,
        moveToTrash,
        restoreSelected,
        publishSelected,
        unpublishSelected,
        editSelected,
        openDeleteModal,
        openEmptyTrashModal,
        confirmAction,
        showNotification,
        toggleHomepage
    };
}
