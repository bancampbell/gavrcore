import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { usersApi } from '../../infrastructure/api/user-api';

export function useUsers(props: any) {
    const filters = ref({
        search: props.filters?.search || '',
        blocked: props.filters?.blocked as boolean | undefined,
        activated: props.filters?.activated as boolean | undefined,
    });

    const selectedUsers = ref<number[]>([]);
    const currentUserId = computed(() => props.user?.id ?? null);

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
    const loading = ref(false);
    const deleteModalOpen = ref(false);
    const deleteLoading = ref(false);
    const userToDelete = ref<any>(null);
    const deleteMessage = ref('');

    const bulkModalOpen = ref(false);
    const bulkModalTitle = ref('');
    const bulkModalMessage = ref('');
    const bulkAction = ref<null | (() => Promise<void>)>(null);

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

    // Пагинация приходит из users.meta.* (ресурс-коллекция над пагинатором)
    const prevPage = () => {
        const currentPage = props.users?.meta?.current_page ?? 1;
        if (currentPage > 1) {
            router.get('/admin/users', { ...filters.value, page: currentPage - 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const nextPage = () => {
        const currentPage = props.users?.meta?.current_page ?? 1;
        const lastPage = props.users?.meta?.last_page ?? 1;
        if (currentPage < lastPage) {
            router.get('/admin/users', { ...filters.value, page: currentPage + 1 }, {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const editSelected = () => {
        if (selectedUsers.value.length === 1) {
            router.visit(`/admin/users/${selectedUsers.value[0]}/edit`);
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

    const selectionIncludesCurrentUser = (): boolean => {
        if (currentUserId.value === null) return false;
        return selectedUsers.value.includes(Number(currentUserId.value));
    };

    const openDeleteModalForSelected = () => {
        if (selectedUsers.value.length === 0) return;

        // Если в выборке есть текущий пользователь — не начинаем даже
        // частичное удаление. Сервер всё равно запретит self-delete,
        // но здесь не должно удалиться N-1 записей до 403.
        if (selectionIncludesCurrentUser()) {
            showNotification('Нельзя удалить самого себя', 'error');
            return;
        }

        if (selectedUsers.value.length === 1) {
            const user = props.users.data.find((u: any) => u.id === selectedUsers.value[0]);
            if (user) {
                openDeleteModal(user);
            }
        } else {
            showBulkModal(
                'Массовое удаление',
                `Вы уверены, что хотите удалить ${selectedUsers.value.length} пользователя(ей)?`,
                async () => {
                    loading.value = true;
                    try {
                        for (const id of selectedUsers.value) {
                            await usersApi.delete(id);
                        }
                        showNotification(`${selectedUsers.value.length} пользователь(ей) удалено`);
                        selectedUsers.value = [];
                        applyFilters();
                    } catch (error: any) {
                        showNotification(error.response?.data?.message || 'Ошибка', 'error');
                    } finally {
                        loading.value = false;
                    }
                }
            );
        }
    };

    return {
        filters,
        selectedUsers,
        allSelected,
        notification,
        loading,
        deleteModalOpen,
        deleteLoading,
        deleteMessage,
        bulkModalOpen,
        bulkModalTitle,
        bulkModalMessage,
        applyFilters,
        debounceSearch,
        resetFilters,
        prevPage,
        nextPage,
        editSelected,
        openDeleteModal,
        bulkBlock,
        bulkUnblock,
        confirmDeleteHandler,
        confirmBulkAction,
        showNotification,
        openDeleteModalForSelected,
    };
}
