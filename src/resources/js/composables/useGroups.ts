import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { groupsApi } from '@/api/groups';

export function useGroups(props: any) {
    const filters = ref({
        search: props.filters?.search || '',
        status: props.filters?.status as boolean | undefined,
    });

    const selectedGroups = ref<number[]>([]);
    const allSelected = computed({
        get: () => {
            if (!props.groups?.data?.length) return false;
            return selectedGroups.value.length === props.groups.data.length;
        },
        set: (val: boolean) => {
            if (val) {
                selectedGroups.value = props.groups.data.map((g: any) => g.id);
            } else {
                selectedGroups.value = [];
            }
        },
    });

    const notification = ref({ show: false, message: '', type: 'success' });
    const modalOpen = ref(false);
    const editingId = ref<number | null>(null);
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const groupToDelete = ref<any>(null);
    const deleteMessage = ref('');

    const bulkModalOpen = ref(false);
    const bulkModalTitle = ref('');
    const bulkModalMessage = ref('');
    const bulkAction = ref<null | (() => Promise<void>)>(null);

    const form = ref({
        name: '',
        alias: '',
        description: '',
        status: true,
        ordering: 0,
    });

    let searchTimeout: any = null;

    const showNotification = (message: string, type: string = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    // Исправленный applyFilters
    const applyFilters = () => {
        const params: any = {};

        if (filters.value.search) {
            params.search = filters.value.search;
        }

        if (filters.value.status !== undefined && filters.value.status !== null) {
            params.status = filters.value.status;
        }

        router.get('/admin/groups', params, {
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
        filters.value = { search: '', status: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (props.groups?.current_page > 1) {
            const params: any = {
                page: props.groups.current_page - 1
            };
            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.status !== undefined && filters.value.status !== null) {
                params.status = filters.value.status;
            }
            router.get('/admin/groups', params, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const nextPage = () => {
        if (props.groups?.current_page < props.groups?.last_page) {
            const params: any = {
                page: props.groups.current_page + 1
            };
            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.status !== undefined && filters.value.status !== null) {
                params.status = filters.value.status;
            }
            router.get('/admin/groups', params, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const openCreateModal = () => {
        editingId.value = null;
        form.value = { name: '', alias: '', description: '', status: true, ordering: 0 };
        modalOpen.value = true;
    };

    const openEditModal = (group: any) => {
        editingId.value = group.id;
        form.value = {
            name: group.name,
            alias: group.alias || '',
            description: group.description || '',
            status: group.status,
            ordering: group.ordering || 0,
        };
        modalOpen.value = true;
    };

    const openEditSelectedModal = () => {
        if (selectedGroups.value.length === 1) {
            const group = props.groups.data.find((g: any) => g.id === selectedGroups.value[0]);
            if (group) openEditModal(group);
        }
    };

    const submitForm = async () => {
        loading.value = true;
        try {
            const submitData: any = {
                name: form.value.name,
                description: form.value.description,
                status: form.value.status,
                ordering: form.value.ordering,
            };

            if (form.value.alias && form.value.alias.trim() !== '') {
                submitData.alias = form.value.alias;
            }

            if (editingId.value) {
                await groupsApi.update(editingId.value, submitData);
                showNotification('Группа обновлена');
            } else {
                await groupsApi.create(submitData);
                showNotification('Группа создана');
            }
            modalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openDeleteModal = (group: any) => {
        groupToDelete.value = group;
        deleteMessage.value = `Вы уверены, что хотите удалить группу "${group.name}"?`;
        deleteModalOpen.value = true;
    };

    const openDeleteModalForSelected = () => {
        if (selectedGroups.value.length === 0) return;

        if (selectedGroups.value.length === 1) {
            const group = props.groups.data.find((g: any) => g.id === selectedGroups.value[0]);
            if (group) {
                openDeleteModal(group);
            }
        } else {
            bulkDelete();
        }
    };

    const confirmDeleteHandler = async () => {
        if (!groupToDelete.value) return;
        deleteLoading.value = true;
        try {
            await groupsApi.delete(groupToDelete.value.id);
            showNotification('Группа удалена');
            deleteModalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            deleteLoading.value = false;
            groupToDelete.value = null;
        }
    };

    const showBulkModal = (title: string, message: string, action: () => Promise<void>) => {
        bulkModalTitle.value = title;
        bulkModalMessage.value = message;
        bulkAction.value = action;
        bulkModalOpen.value = true;
    };

    const confirmBulkAction = async () => {
        if (bulkAction.value) {
            await bulkAction.value();
        }
        bulkModalOpen.value = false;
        bulkAction.value = null;
    };

    const bulkDelete = () => {
        if (selectedGroups.value.length === 0) return;
        showBulkModal(
            'Удаление групп',
            `Вы уверены, что хотите удалить ${selectedGroups.value.length} группу(ы)?`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedGroups.value) {
                        await groupsApi.delete(id);
                    }
                    showNotification(`${selectedGroups.value.length} групп(ы) удалено`);
                    selectedGroups.value = [];
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
        if (selectedGroups.value.length === 0) return;
        showBulkModal(
            'Публикация групп',
            `Вы уверены, что хотите опубликовать ${selectedGroups.value.length} группу(ы)?`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedGroups.value) {
                        await groupsApi.updateStatus(id, true);
                    }
                    showNotification(`${selectedGroups.value.length} групп(ы) опубликовано`);
                    selectedGroups.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка', 'error');
                } finally {
                    loading.value = false;
                }
            }
        );
    };

    const bulkUnpublish = () => {
        if (selectedGroups.value.length === 0) return;
        showBulkModal(
            'Снятие с публикации',
            `Вы уверены, что хотите снять с публикации ${selectedGroups.value.length} группу(ы)?`,
            async () => {
                loading.value = true;
                try {
                    for (const id of selectedGroups.value) {
                        await groupsApi.updateStatus(id, false);
                    }
                    showNotification(`${selectedGroups.value.length} групп(ы) снято с публикации`);
                    selectedGroups.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка', 'error');
                } finally {
                    loading.value = false;
                }
            }
        );
    };

    return {
        filters,
        selectedGroups,
        allSelected,
        notification,
        modalOpen,
        editingId,
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
        openDeleteModalForSelected,
        bulkDelete,
        bulkPublish,
        bulkUnpublish,
        confirmDeleteHandler,
        confirmBulkAction,
        showNotification,
    };
}
