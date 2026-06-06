import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usersApi } from '@/api/users';

export function useUsers(props: any) {
    const filters = ref({
        search: props.filters?.search || '',
        blocked: props.filters?.blocked as boolean | undefined,
        activated: props.filters?.activated as boolean | undefined,
    });

    const selectedUsers = ref<number[]>([]);
    const allSelected = computed({
        get: () => {
            if (!props.users?.data?.length) return false;
            return selectedUsers.value.length === props.users.data.length;
        },
        set: (val: boolean) => {
            if (val) {
                selectedUsers.value = props.users.data.map((u: any) => u.id);
            } else {
                selectedUsers.value = [];
            }
        },
    });

    const notification = ref({ show: false, message: '', type: 'success' });
    const modalOpen = ref(false);
    const editingId = ref<number | null>(null);
    const editingUser = ref<any>(null);
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const userToDelete = ref<any>(null);
    const deleteMessage = ref('');

    // Модалка для массовых операций
    const bulkModalOpen = ref(false);
    const bulkModalTitle = ref('');
    const bulkModalMessage = ref('');
    const bulkAction = ref<null | (() => Promise<void>)>(null);

    const form = ref({
        name: '',
        username: '',
        email: '',
        password: '',
        blocked: false,
        activated: true,
        groups: [] as number[],
    });

    let searchTimeout: any = null;

    const showNotification = (message: string, type: string = 'success') => {
        notification.value = { show: true, message, type };
        setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const applyFilters = () => {
        const params: any = {};

        if (filters.value.search) {
            params.search = filters.value.search;
        }
        if (filters.value.blocked !== undefined) {
            params.blocked = filters.value.blocked;
        }
        if (filters.value.activated !== undefined) {
            params.activated = filters.value.activated;
        }

        router.get('/admin/users', params, {
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
        filters.value = { search: '', blocked: undefined, activated: undefined };
        applyFilters();
    };

    const prevPage = () => {
        if (props.users?.current_page > 1) {
            router.get('/admin/users', { ...filters.value, page: props.users.current_page - 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const nextPage = () => {
        if (props.users?.current_page < props.users?.last_page) {
            router.get('/admin/users', { ...filters.value, page: props.users.current_page + 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const openCreateModal = () => {
        editingId.value = null;
        editingUser.value = null;
        form.value = { name: '', username: '', email: '', password: '', blocked: false, activated: true, groups: [] };
        modalOpen.value = true;
    };

    const openEditModal = (user: any) => {
        editingId.value = user.id;
        editingUser.value = user;
        form.value = {
            name: user.name,
            username: user.username,
            email: user.email,
            password: '',
            blocked: user.blocked,
            activated: user.activated,
            groups: user.groups?.map((g: any) => g.id) || [],
        };
        modalOpen.value = true;
    };

    const openEditSelectedModal = () => {
        if (selectedUsers.value.length === 1) {
            const user = props.users.data.find((u: any) => u.id === selectedUsers.value[0]);
            if (user) openEditModal(user);
        }
    };

    const submitForm = async () => {
        loading.value = true;
        try {
            const submitData: any = {
                name: form.value.name,
                username: form.value.username,
                email: form.value.email,
                blocked: form.value.blocked,
                activated: form.value.activated,
                groups: form.value.groups,
            };

            if (form.value.password && form.value.password.trim() !== '') {
                submitData.password = form.value.password;
            }

            if (editingId.value) {
                await usersApi.update(editingId.value, submitData);
                showNotification('Пользователь обновлён');
            } else {
                await usersApi.create(submitData);
                showNotification('Пользователь создан');
            }
            modalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка', 'error');
        } finally {
            loading.value = false;
        }
    };

    const openDeleteModal = (user: any) => {
        userToDelete.value = user;
        deleteMessage.value = `Вы уверены, что хотите удалить пользователя "${user.name}"?`;
        deleteModalOpen.value = true;
    };

    const confirmDeleteHandler = async () => {
        if (!userToDelete.value) return;
        deleteLoading.value = true;
        try {
            await usersApi.delete(userToDelete.value.id);
            showNotification('Пользователь удалён');
            deleteModalOpen.value = false;
            applyFilters();
        } catch (error: any) {
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
        } finally {
            deleteLoading.value = false;
            userToDelete.value = null;
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

    const bulkBlock = () => {
        if (selectedUsers.value.length === 0) return;
        showBulkModal(
            'Блокировка пользователей',
            `Вы уверены, что хотите заблокировать ${selectedUsers.value.length} пользователя(ей)?`,
            async () => {
                loading.value = true;
                try {
                    await usersApi.bulkBlock(selectedUsers.value);
                    showNotification(`${selectedUsers.value.length} пользователь(ей) заблокирован(о)`);
                    selectedUsers.value = [];
                    applyFilters();
                } catch (error: any) {
                    showNotification(error.response?.data?.message || 'Ошибка', 'error');
                } finally {
                    loading.value = false;
                }
            }
        );
    };

    const bulkUnblock = () => {
        if (selectedUsers.value.length === 0) return;
        showBulkModal(
            'Разблокировка пользователей',
            `Вы уверены, что хотите разблокировать ${selectedUsers.value.length} пользователя(ей)?`,
            async () => {
                loading.value = true;
                try {
                    await usersApi.bulkUnblock(selectedUsers.value);
                    showNotification(`${selectedUsers.value.length} пользователь(ей) разблокирован(о)`);
                    selectedUsers.value = [];
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
        selectedUsers,
        allSelected,
        notification,
        modalOpen,
        editingId,
        editingUser,
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
        bulkBlock,
        bulkUnblock,
        confirmDeleteHandler,
        confirmBulkAction,
        showNotification,
    };
}
