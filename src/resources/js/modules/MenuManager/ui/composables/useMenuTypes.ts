import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { menuTypesApi } from '@/modules/MenuManager/infrastructure/api/menu-api';
import type { MenuType } from '@/modules/MenuManager/infrastructure/api/menu-api';

export interface MenuTypesData {
    data: MenuType[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export interface Filters {
    search?: string;
    status?: boolean;
}

export function useMenuTypes(props: { menuTypes: MenuTypesData; filters?: Filters }) {
    const filters = ref({
        search: props.filters?.search || '',
        status: props.filters?.status as boolean | undefined,
    });

    const selectedMenuTypes = ref<number[]>([]);
    const allSelected = computed({
        get: () => {
            if (!props.menuTypes.data.length) return false;
            return selectedMenuTypes.value.length === props.menuTypes.data.length;
        },
        set: (val: boolean) => {
            if (val) selectedMenuTypes.value = props.menuTypes.data.map((t) => t.id);
            else selectedMenuTypes.value = [];
        },
    });

    const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
    const modalOpen = ref(false);
    const editingId = ref<number | null>(null);
    const editingMenuType = ref<MenuType | null>(null);
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const categoryToDelete = ref<MenuType | null>(null);
    const deleteMessage = ref('');

    const bulkModalOpen = ref(false);
    const bulkModalTitle = ref('');
    const bulkModalMessage = ref('');
    const bulkAction = ref<null | (() => Promise<void>)>(null);

    const form = ref({ title: '', alias: '', description: '', status: true });
    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const applyFilters = () => {
        router.get('/admin/menu/types', filters.value, { preserveState: true, preserveScroll: true });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => applyFilters(), 500);
    };

    const resetFilters = () => {
        filters.value = { search: '', status: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (props.menuTypes.current_page > 1) {
            router.get(
                '/admin/menu/types',
                { ...filters.value, page: props.menuTypes.current_page - 1 },
                { preserveState: true, preserveScroll: true }
            );
        }
    };

    const nextPage = () => {
        if (props.menuTypes.current_page < props.menuTypes.last_page) {
            router.get(
                '/admin/menu/types',
                { ...filters.value, page: props.menuTypes.current_page + 1 },
                { preserveState: true, preserveScroll: true }
            );
        }
    };

    const openCreateModal = () => {
        editingId.value = null;
        editingMenuType.value = null;
        form.value = { title: '', alias: '', description: '', status: true };
        modalOpen.value = true;
    };

    const openEditModal = (type: MenuType) => {
        editingId.value = type.id;
        editingMenuType.value = type;
        form.value = {
            title: type.title,
            alias: type.alias || '',
            description: type.description || '',
            status: type.status,
        };
        modalOpen.value = true;
    };

    const openEditSelectedModal = () => {
        if (selectedMenuTypes.value.length === 1) {
            const type = props.menuTypes.data.find((t) => t.id === selectedMenuTypes.value[0]);
            if (type) openEditModal(type);
        }
    };

    const submitForm = async () => {
        loading.value = true;
        try {
            const submitData: Record<string, unknown> = {
                title: form.value.title,
                description: form.value.description,
                status: form.value.status,
            };
            if (form.value.alias && form.value.alias.trim() !== '') submitData.alias = form.value.alias;
            if (editingId.value) {
                await menuTypesApi.update(editingId.value, submitData as Partial<MenuType>);
                showNotification('Тип меню обновлен');
            } else {
                await menuTypesApi.create(submitData as Partial<MenuType>);
                showNotification('Тип меню создан');
            }
            modalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openDeleteModal = (type: MenuType) => {
        categoryToDelete.value = type;
        deleteMessage.value = `Вы уверены, что хотите удалить тип меню "${type.title}"? Все пункты меню также будут удалены.`;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        if (!categoryToDelete.value) return;
        deleteLoading.value = true;
        try {
            await menuTypesApi.delete(categoryToDelete.value.id);
            showNotification('Тип меню удален');
            deleteModalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            deleteLoading.value = false;
            categoryToDelete.value = null;
        }
    };

    const showBulkModal = (title: string, message: string, action: () => Promise<void>) => {
        bulkModalTitle.value = title;
        bulkModalMessage.value = message;
        bulkAction.value = action;
        bulkModalOpen.value = true;
    };

    const confirmBulkAction = async () => {
        if (bulkAction.value) await bulkAction.value();
        bulkModalOpen.value = false;
        bulkAction.value = null;
    };

    const bulkDelete = () => {
        if (selectedMenuTypes.value.length === 0) return;
        showBulkModal(
            'Удаление типов меню',
            `Вы уверены, что хотите удалить ${selectedMenuTypes.value.length} тип(ов) меню? Все пункты меню также будут удалены.`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedMenuTypes.value) await menuTypesApi.delete(id);
                    showNotification(`${selectedMenuTypes.value.length} тип(ов) меню удалено`);
                    selectedMenuTypes.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
                } finally {
                    loading.value = false;
                }
            }
        );
    };

    const bulkPublish = () => {
        if (selectedMenuTypes.value.length === 0) return;
        showBulkModal(
            'Публикация типов меню',
            `Вы уверены, что хотите опубликовать ${selectedMenuTypes.value.length} тип(ов) меню?`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedMenuTypes.value) {
                        await menuTypesApi.updateStatus(id, true);
                    }
                    showNotification(`${selectedMenuTypes.value.length} тип(ов) меню опубликовано`);
                    selectedMenuTypes.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка', 'error');
                    loading.value = false;
                }
            }
        );
    };

    const bulkUnpublish = () => {
        if (selectedMenuTypes.value.length === 0) return;
        showBulkModal(
            'Снятие с публикации',
            `Вы уверены, что хотите снять с публикации ${selectedMenuTypes.value.length} тип(ов) меню?`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedMenuTypes.value) {
                        await menuTypesApi.updateStatus(id, false);
                    }
                    showNotification(`${selectedMenuTypes.value.length} тип(ов) меню снято с публикации`);
                    selectedMenuTypes.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка', 'error');
                    loading.value = false;
                }
            }
        );
    };

    return {
        filters,
        selectedMenuTypes,
        allSelected,
        notification,
        modalOpen,
        editingId,
        editingMenuType,
        loading,
        deleteModalOpen,
        deleteLoading,
        form,
        deleteMessage,
        bulkModalOpen,
        bulkModalTitle,
        bulkModalMessage,
        applyFilters,
        debounceSearch,
        resetFilters,
        prevPage,
        nextPage,
        openCreateModal,
        openEditModal,
        openEditSelectedModal,
        submitForm,
        openDeleteModal,
        bulkDelete,
        bulkPublish,
        bulkUnpublish,
        confirmDeleteHandler,
        confirmBulkAction,
        showNotification,
    };
}
