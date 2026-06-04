import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { menuTypesApi } from '@/api/menu';

export function useMenuTypes(props: any) {
    const filters = ref({
        search: props.filters?.search || '',
        status: props.filters?.status as boolean | undefined,
    });

    const selectedMenuTypes = ref<number[]>([]);
    const allSelected = computed({
        get: () => {
            if (!props.menuTypes?.data?.length) return false;
            return selectedMenuTypes.value.length === props.menuTypes.data.length;
        },
        set: (val: boolean) => {
            if (val) {
                selectedMenuTypes.value = props.menuTypes.data.map((t: any) => t.id);
            } else {
                selectedMenuTypes.value = [];
            }
        },
    });

    const notification = ref({ show: false, message: '', type: 'success' });
    const modalOpen = ref(false);
    const editingId = ref<number | null>(null);
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const categoryToDelete = ref<any>(null);

    const form = ref({
        title: '',
        alias: '',
        description: '',
        status: true,
    });

    const deleteMessage = ref('');

    let searchTimeout: any = null;

    const showNotification = (message: string, type: string = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const applyFilters = () => {
        router.get('/admin/menu', filters.value, {
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
        if (props.menuTypes?.current_page > 1) {
            router.get('/admin/menu', { ...filters.value, page: props.menuTypes.current_page - 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const nextPage = () => {
        if (props.menuTypes?.current_page < props.menuTypes?.last_page) {
            router.get('/admin/menu', { ...filters.value, page: props.menuTypes.current_page + 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const openCreateModal = () => {
        editingId.value = null;
        form.value = { title: '', alias: '', description: '', status: true };
        modalOpen.value = true;
    };

    const openEditModal = (type: any) => {
        editingId.value = type.id;
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
            const type = props.menuTypes.data.find((t: any) => t.id === selectedMenuTypes.value[0]);
            if (type) openEditModal(type);
        }
    };

    const submitForm = async () => {
        loading.value = true;
        try {
            const submitData: any = {
                title: form.value.title,
                description: form.value.description,
                status: form.value.status,
            };

            if (form.value.alias && form.value.alias.trim() !== '') {
                submitData.alias = form.value.alias;
            }

            if (editingId.value) {
                await menuTypesApi.update(editingId.value, submitData);
                showNotification('Тип меню обновлен');
            } else {
                await menuTypesApi.create(submitData);
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

    const openDeleteModal = (type: any) => {
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

    const bulkDelete = async () => {
        if (selectedMenuTypes.value.length === 0) return;

        if (!confirm(`Удалить ${selectedMenuTypes.value.length} тип(ов) меню?`)) return;

        loading.value = true;
        try {
            for (const id of selectedMenuTypes.value) {
                await menuTypesApi.delete(id);
            }
            showNotification(`${selectedMenuTypes.value.length} тип(ов) меню удалено`);
            selectedMenuTypes.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            loading.value = false;
        }
    };

    const bulkPublish = async () => {
        if (selectedMenuTypes.value.length === 0) return;

        loading.value = true;
        try {
            for (const id of selectedMenuTypes.value) {
                const type = props.menuTypes.data.find((t: any) => t.id === id);
                if (type) {
                    const submitData: any = {
                        title: type.title,
                        description: type.description || '',
                        status: true,
                    };
                    if (type.alias) {
                        submitData.alias = type.alias;
                    }
                    await menuTypesApi.update(id, submitData);
                }
            }
            showNotification(`${selectedMenuTypes.value.length} тип(ов) меню опубликовано`);
            selectedMenuTypes.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const bulkUnpublish = async () => {
        if (selectedMenuTypes.value.length === 0) return;

        loading.value = true;
        try {
            for (const id of selectedMenuTypes.value) {
                const type = props.menuTypes.data.find((t: any) => t.id === id);
                if (type) {
                    const submitData: any = {
                        title: type.title,
                        description: type.description || '',
                        status: false,
                    };
                    if (type.alias) {
                        submitData.alias = type.alias;
                    }
                    await menuTypesApi.update(id, submitData);
                }
            }
            showNotification(`${selectedMenuTypes.value.length} тип(ов) меню снято с публикации`);
            selectedMenuTypes.value = [];
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    return {
        filters,
        selectedMenuTypes,
        allSelected,
        notification,
        modalOpen,
        editingId,
        loading,
        deleteModalOpen,
        deleteLoading,
        form,
        deleteMessage,
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
    };
}
