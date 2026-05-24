import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { categoriesApi } from '../api/categories';
import type { Category, CategoriesData, CategoryFormData, CategoryFilters, Notification } from '../types';

interface UseCategoriesProps {
    categories: CategoriesData;
    filters?: CategoryFilters;
}

export function useCategories(props: UseCategoriesProps) {
    const filters = ref<CategoryFilters>({
        search: props.filters?.search || ''
    });

    const selectedCategories = ref<number[]>([]);
    const allSelected = ref<boolean>(false);
    const notification = ref<Notification>({ show: false, message: '', type: 'success' });
    const modalOpen = ref<boolean>(false);
    const editingId = ref<number | null>(null);
    const loading = ref<boolean>(false);
    const deleteModalOpen = ref<boolean>(false);
    const categoryToDelete = ref<Category | null>(null);
    const deleteLoading = ref<boolean>(false);
    const form = ref<CategoryFormData>({
        name: '',
        alias: '',
        description: '',
        parent_id: null
    });

    let notificationTimeout: ReturnType<typeof setTimeout> | null = null;
    let searchTimeout: ReturnType<typeof setTimeout> | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
        notification.value = { show: true, message, type };
        notificationTimeout = setTimeout(() => {
            notification.value.show = false;
        }, 5000);
    };

    const getIndent = (category: Category): string => {
        let indent = '';
        if (category.depth > 0) {
            indent = '— '.repeat(category.depth);
        }
        return indent;
    };

    const selectAll = () => {
        if (allSelected.value) {
            selectedCategories.value = props.categories.data.map(c => c.id);
        } else {
            selectedCategories.value = [];
        }
    };

    watch(selectedCategories, (val) => {
        allSelected.value = val.length === props.categories.data?.length;
    });

    const applyFilters = () => {
        router.get('/admin/categories', filters.value, {
            preserveState: true,
            preserveScroll: true
        });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    };

    const resetFilters = () => {
        filters.value = { search: '' };
        applyFilters();
    };

    const prevPage = () => {
        if (props.categories.current_page > 1) {
            router.get(`/admin/categories?page=${props.categories.current_page - 1}`, filters.value);
        }
    };

    const nextPage = () => {
        if (props.categories.current_page < props.categories.last_page) {
            router.get(`/admin/categories?page=${props.categories.current_page + 1}`, filters.value);
        }
    };

    const openCreateModal = () => {
        editingId.value = null;
        form.value = {
            name: '',
            alias: '',
            description: '',
            parent_id: null
        };
        modalOpen.value = true;
    };

    const openEditModal = (category: Category) => {
        editingId.value = category.id;
        form.value = {
            name: category.name,
            alias: category.alias || '',
            description: category.description || '',
            parent_id: category.parent_id || null
        };
        modalOpen.value = true;
    };

    const openEditSelectedModal = () => {
        if (selectedCategories.value.length !== 1) return;
        const category = props.categories.data.find(c => c.id === selectedCategories.value[0]);
        if (category) {
            openEditModal(category);
        }
    };

    const submitForm = async () => {
        loading.value = true;

        try {
            if (editingId.value) {
                await categoriesApi.update(editingId.value, form.value);
                showNotification('Категория обновлена', 'success');
            } else {
                await categoriesApi.create(form.value);
                showNotification('Категория создана', 'success');
            }
            modalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка при сохранении', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openDeleteModal = (category: Category) => {
        categoryToDelete.value = category;
        deleteModalOpen.value = true;
    };

    const bulkDelete = () => {
        if (selectedCategories.value.length === 0) return;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        deleteLoading.value = true;

        try {
            if (categoryToDelete.value) {
                await categoriesApi.delete(categoryToDelete.value.id);
                showNotification('Категория удалена', 'success');
                categoryToDelete.value = null;
            } else if (selectedCategories.value.length > 0) {
                await categoriesApi.bulkDelete(selectedCategories.value);
                showNotification(`${selectedCategories.value.length} категорий удалено`, 'success');
                selectedCategories.value = [];
            }
            deleteModalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification('Ошибка при удалении', 'error');
        } finally {
            deleteLoading.value = false;
        }
    };

    const deleteMessage = computed(() => {
        if (categoryToDelete.value) {
            return `Вы уверены, что хотите удалить категорию «${categoryToDelete.value.name}»? Все подкатегории также будут удалены.`;
        }
        if (selectedCategories.value.length > 0) {
            return `Вы уверены, что хотите удалить ${selectedCategories.value.length} выбранных категорий? Все подкатегории также будут удалены.`;
        }
        return '';
    });

    return {
        filters,
        selectedCategories,
        allSelected,
        notification,
        modalOpen,
        editingId,
        loading,
        deleteModalOpen,
        categoryToDelete,
        deleteLoading,
        form,
        deleteMessage,
        getIndent,
        selectAll,
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
        confirmDeleteHandler,
        showNotification
    };
}
