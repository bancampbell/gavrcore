import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { menuItemsApi, menuTypesApi, type MenuItem, type MenuType } from '@/modules/MenuManager/infrastructure/api/menu-api';

export function useMenuItems() {
    const menuTypes = ref<MenuType[]>([]);
    const selectedMenuTypeId = ref<number | null>(null);
    const flatItems = ref<(MenuItem & { level: number })[]>([]);
    const selectedItems = ref<number[]>([]);
    const pagination = ref({ current_page: 1, last_page: 1, from: 0, to: 0, total: 0 });

    const allSelected = computed({
        get: () => {
            if (!flatItems.value.length) return false;
            return selectedItems.value.length === flatItems.value.length;
        },
        set: (val: boolean) => {
            if (val) selectedItems.value = flatItems.value.map((i) => i.id);
            else selectedItems.value = [];
        },
    });

    const filters = ref({ search: '', status: undefined as boolean | undefined });
    const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const itemToDelete = ref<MenuItem | null>(null);
    const deleteMessage = ref('');
    const bulkDeleteModalOpen = ref(false);
    const bulkDeleteLoading = ref(false);
    const bulkDeleteMessage = ref('');
    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const toggleSelect = (id: number) => {
        const index = selectedItems.value.indexOf(id);
        if (index === -1) selectedItems.value.push(id);
        else selectedItems.value.splice(index, 1);
    };

    const getLinkTypeLabel = (type: string) => {
        const labels: Record<string, string> = {
            url: 'URL',
            material: 'Материал',
            separator: 'Разделитель',
            heading: 'Заголовок',
            external: 'Внешний URL',
        };
        return labels[type] || type;
    };

    const getLinkValueDisplay = (type: string, value: string | null) => {
        if (!value) return '—';
        if (type === 'material') return `ID: ${value}`;
        return value;
    };

    const loadMenuTypes = async () => {
        try {
            const r = await menuTypesApi.getAll({ per_page: 100 });
            menuTypes.value = r.data.data || [];
        } catch (e) {
            console.error(e);
        }
    };

    const loadAllItems = async () => {
        try {
            const params: Record<string, any> = { page: pagination.value.current_page };
            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.status !== undefined) params.status = filters.value.status;

            const r = await menuItemsApi.getAllItems(params);
            const d = r.data;

            pagination.value = {
                current_page: d.current_page,
                last_page: d.last_page,
                from: d.from || 0,
                to: d.to || 0,
                total: d.total,
            };
            flatItems.value = d.data.map((item: MenuItem) => ({ ...item, level: 0 }));
        } catch (e) {
            console.error(e);
            flatItems.value = [];
            pagination.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
        }
    };

    const flattenTree = (items: MenuItem[], level = 0): (MenuItem & { level: number })[] => {
        const result: (MenuItem & { level: number })[] = [];
        for (const item of items) {
            result.push({ ...item, level });
            if (item.children?.length) {
                result.push(...flattenTree(item.children, level + 1));
            }
        }
        return result;
    };

    const filterTree = (
        items: MenuItem[],
        activeFilters: { search?: string; status?: boolean }
    ): MenuItem[] => {
        const searchLower = activeFilters.search?.toLowerCase() || '';

        return items.reduce<MenuItem[]>((acc, item) => {
            const filteredChildren = item.children?.length
                ? filterTree(item.children, activeFilters)
                : [];

            const matchesSearch = !searchLower || item.title.toLowerCase().includes(searchLower);
            const matchesStatus = activeFilters.status === undefined || item.status === activeFilters.status;

            if ((matchesSearch && matchesStatus) || filteredChildren.length > 0) {
                acc.push({ ...item, children: filteredChildren });
            }

            return acc;
        }, []);
    };

    const loadMenuItemsForType = async () => {
        if (!selectedMenuTypeId.value) return;

        try {
            const r = await menuItemsApi.getTree(selectedMenuTypeId.value);
            let tree = r.data || [];

            if (filters.value.search || filters.value.status !== undefined) {
                tree = filterTree(tree, filters.value);
            }

            flatItems.value = flattenTree(tree);
            pagination.value = {
                current_page: 1,
                last_page: 1,
                from: flatItems.value.length ? 1 : 0,
                to: flatItems.value.length,
                total: flatItems.value.length,
            };
        } catch (e) {
            console.error(e);
            flatItems.value = [];
            pagination.value = { current_page: 1, last_page: 1, from: 0, to: 0, total: 0 };
        }
    };

    const loadData = async () => {
        selectedItems.value = [];
        if (selectedMenuTypeId.value === null) {
            await loadAllItems();
        } else {
            await loadMenuItemsForType();
        }
    };

    const applyFilters = () => {
        pagination.value.current_page = 1;
        loadData();
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
        if (pagination.value.current_page > 1) {
            pagination.value.current_page--;
            loadData();
        }
    };

    const nextPage = () => {
        if (pagination.value.current_page < pagination.value.last_page) {
            pagination.value.current_page++;
            loadData();
        }
    };

    const changeMenuType = () => {
        if (selectedMenuTypeId.value) {
            router.visit(`/admin/menu/types/${selectedMenuTypeId.value}/items`);
        } else {
            router.visit('/admin/menu/items/all');
        }
    };

    const openEditSelected = () => {
        if (selectedItems.value.length === 1) {
            router.visit(`/admin/menu/items/${selectedItems.value[0]}/edit`);
        }
    };

    const updateLocalStatus = (ids: number[], status: boolean) => {
        flatItems.value.forEach((item) => {
            if (ids.includes(item.id)) item.status = status;
        });
    };

    const handleBulkPublish = async () => {
        if (selectedItems.value.length === 0) return;
        loading.value = true;
        try {
            for (const id of selectedItems.value) {
                await menuItemsApi.updateStatus(id, true);
            }
            updateLocalStatus(selectedItems.value, true);
            showNotification(`${selectedItems.value.length} пункт(ов) меню опубликовано`, 'success');
            selectedItems.value = [];
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const handleBulkUnpublish = async () => {
        if (selectedItems.value.length === 0) return;
        loading.value = true;
        try {
            for (const id of selectedItems.value) {
                await menuItemsApi.updateStatus(id, false);
            }
            updateLocalStatus(selectedItems.value, false);
            showNotification(`${selectedItems.value.length} пункт(ов) меню снято с публикации`, 'success');
            selectedItems.value = [];
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openDeleteModal = (item: MenuItem) => {
        itemToDelete.value = item;
        deleteMessage.value = `Вы уверены, что хотите удалить пункт "${item.title}"? Все дочерние пункты также будут удалены.`;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        if (!itemToDelete.value) return;
        deleteLoading.value = true;
        try {
            await menuItemsApi.delete(itemToDelete.value.id);
            showNotification('Пункт меню удален', 'success');
            deleteModalOpen.value = false;
            await loadData();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            deleteLoading.value = false;
            itemToDelete.value = null;
        }
    };

    const openDeleteModalForSelected = () => {
        if (selectedItems.value.length === 0) return;
        if (selectedItems.value.length === 1) {
            const item = flatItems.value.find((i) => i.id === selectedItems.value[0]);
            if (item) openDeleteModal(item);
        } else {
            bulkDeleteMessage.value = `Вы уверены, что хотите удалить ${selectedItems.value.length} пункт(ов) меню? Все дочерние пункты также будут удалены.`;
            bulkDeleteModalOpen.value = true;
        }
    };

    const confirmBulkDeleteHandler = async () => {
        if (selectedItems.value.length === 0) return;
        bulkDeleteLoading.value = true;
        try {
            for (const id of selectedItems.value) {
                await menuItemsApi.delete(id);
            }
            showNotification(`${selectedItems.value.length} пункт(ов) меню удалено`, 'success');
            selectedItems.value = [];
            bulkDeleteModalOpen.value = false;
            await loadData();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            bulkDeleteLoading.value = false;
        }
    };

    return {
        menuTypes,
        selectedMenuTypeId,
        flatItems,
        selectedItems,
        allSelected,
        pagination,
        filters,
        notification,
        loading,
        deleteModalOpen,
        deleteLoading,
        deleteMessage,
        bulkDeleteModalOpen,
        bulkDeleteLoading,
        bulkDeleteMessage,
        itemToDelete,
        showNotification,
        toggleSelect,
        getLinkTypeLabel,
        getLinkValueDisplay,
        loadMenuTypes,
        loadData,
        applyFilters,
        debounceSearch,
        resetFilters,
        prevPage,
        nextPage,
        changeMenuType,
        openEditSelected,
        handleBulkPublish,
        handleBulkUnpublish,
        openDeleteModal,
        confirmDeleteHandler,
        openDeleteModalForSelected,
        confirmBulkDeleteHandler,
    };
}
