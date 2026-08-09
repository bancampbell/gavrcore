import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { materialApi } from '../../infrastructure/api/material-api';
import { useMaterialFilters } from './useMaterialFilters';
import { useMaterialSelection } from './useMaterialSelection';
import { useMaterialModals } from './useMaterialModals';
import { useMaterialNotifications } from './useMaterialNotifications';
import type { Material, MaterialsData, MaterialFilters } from '../types';

export function useMaterials(props: {
    materials: MaterialsData;
    filters?: MaterialFilters;
    perPage?: number;
    isLanding?: boolean;
}, baseUrl: string = '/admin/materials') {
    const materialsData = computed(() => props.materials);

    const items = ref<Material[]>(props.materials.data ? [...props.materials.data] : []);

    watch(
        () => props.materials.data,
        (newData) => {
            items.value = newData ? [...newData] : [];
        },
        { immediate: true }
    );

    const {
        filters,
        perPage,
        applyFilters,
        debounceSearch,
        resetFilters,
        changePerPage,
        prevPage,
        nextPage,
        toggleSort,
    } = useMaterialFilters(props, baseUrl);

    const {
        selectedMaterials,
        allSelected,
        toggleSelect,
        selectAll,
    } = useMaterialSelection(props);

    const {
        modalOpen,
        modalTitle,
        modalMessage,
        modalConfirmText,
        modalLoading,
        pendingAction,
        openRestoreModal,
        openDeleteModal,
        openEmptyTrashModal,
        closeModal,
    } = useMaterialModals();

    const {
        notification,
        showNotification,
    } = useMaterialNotifications();

    const togglingHomepageIds = ref<number[]>([]);

    const formatDate = (date: string): string => {
        if (!date) return '';
        const d = new Date(date);
        return `${d.getDate().toString().padStart(2, '0')}.${(d.getMonth() + 1).toString().padStart(2, '0')}.${d.getFullYear().toString().slice(-2)} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}`;
    };

    const moveToTrash = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialApi.moveToTrash(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters(1);
        } catch (error: unknown) {
            const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Ошибка при перемещении в корзину';
            showNotification(message, 'error');
        }
    };

    const restoreSelected = () => {
        if (selectedMaterials.value.length === 0) {
            showNotification('Выберите материалы для восстановления', 'error');
            return;
        }
        openRestoreModal(selectedMaterials.value.length);
    };

    const publishSelected = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialApi.publish(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters(1);
        } catch (error: unknown) {
            const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Ошибка при публикации';
            showNotification(message, 'error');
        }
    };

    const unpublishSelected = async () => {
        if (selectedMaterials.value.length === 0) return;

        try {
            const response = await materialApi.unpublish(selectedMaterials.value);
            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            applyFilters(1);
        } catch (error: unknown) {
            const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Ошибка при снятии с публикации';
            showNotification(message, 'error');
        }
    };

    const toggleHomepage = async (material: Material) => {
        if (togglingHomepageIds.value.includes(material.id)) {
            return;
        }

        const index = items.value.findIndex(m => m.id === material.id);
        if (index === -1) return;

        togglingHomepageIds.value.push(material.id);
        const originalItems = items.value.map(i => ({ ...i }));

        const newValue = !material.show_on_homepage;

        if (newValue) {
            items.value = items.value.map(m => ({
                ...m,
                show_on_homepage: m.id === material.id,
            }));
        } else {
            items.value[index] = { ...items.value[index], show_on_homepage: false };
        }

        try {
            const response = await materialApi.toggleHomepage(material.id, newValue);
            showNotification(response.message, 'success');
        } catch (error: unknown) {
            items.value = originalItems;
            const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Ошибка при обновлении статуса "На главной"';
            showNotification(message, 'error');
        } finally {
            togglingHomepageIds.value = togglingHomepageIds.value.filter(id => id !== material.id);
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

    const confirmAction = async () => {
        modalLoading.value = true;

        try {
            let response: { message: string };
            if (pendingAction.value === 'restore') {
                response = await materialApi.restore(selectedMaterials.value);
            } else if (pendingAction.value === 'delete') {
                response = await materialApi.forceDelete(selectedMaterials.value);
            } else if (pendingAction.value === 'empty') {
                response = await materialApi.emptyTrash();
            } else {
                modalLoading.value = false;
                return;
            }

            showNotification(response.message, 'success');
            selectedMaterials.value = [];
            closeModal();
            applyFilters(1);
        } catch (error: unknown) {
            const message = (error as { response?: { data?: { message?: string } } })?.response?.data?.message || 'Ошибка при выполнении операции';
            showNotification(message, 'error');
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
        toggleSelect,
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
        toggleHomepage,
        toggleSort,
        materialsData,
        items,
        togglingHomepageIds,
    };
}
