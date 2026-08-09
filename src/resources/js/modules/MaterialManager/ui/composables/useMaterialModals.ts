import { ref } from 'vue';

export function useMaterialModals() {
    const modalOpen = ref<boolean>(false);
    const modalTitle = ref<string>('');
    const modalMessage = ref<string>('');
    const modalConfirmText = ref<string>('');
    const modalLoading = ref<boolean>(false);
    const pendingAction = ref<'delete' | 'empty' | 'restore' | null>(null);

    const openRestoreModal = (count: number) => {
        modalTitle.value = 'Восстановление материалов';
        modalMessage.value = count === 1
            ? 'Вы уверены, что хотите восстановить выбранный материал?'
            : `Вы уверены, что хотите восстановить ${count} материалов?`;
        modalConfirmText.value = 'Восстановить';
        pendingAction.value = 'restore';
        modalOpen.value = true;
    };

    const openDeleteModal = (count: number) => {
        modalTitle.value = 'Удаление материалов';
        modalMessage.value = count === 1
            ? 'Вы уверены, что хотите удалить выбранный материал навсегда? Это действие нельзя отменить.'
            : `Вы уверены, что хотите удалить ${count} материалов навсегда? Это действие нельзя отменить.`;
        modalConfirmText.value = 'Удалить навсегда';
        pendingAction.value = 'delete';
        modalOpen.value = true;
    };

    const openEmptyTrashModal = () => {
        modalTitle.value = 'Очистка корзины';
        modalMessage.value = 'Вы уверены, что хотите очистить корзину? Все материалы будут удалены навсегда.';
        modalConfirmText.value = 'Очистить корзину';
        pendingAction.value = 'empty';
        modalOpen.value = true;
    };

    const closeModal = () => {
        modalOpen.value = false;
        pendingAction.value = null;
    };

    return {
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
    };
}
