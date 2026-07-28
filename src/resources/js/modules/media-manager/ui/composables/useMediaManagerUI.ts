import { ref } from 'vue';
import type { MediaItem } from '../../domain/entities/MediaItem';

export function useMediaManagerUI() {
    // Modal states
    const showCreateModal = ref(false);
    const showRenameModal = ref(false);
    const showDeleteModal = ref(false);
    const showUploadModal = ref(false);

    // Data for modals
    const renameItemData = ref<MediaItem | null>(null);
    const deleteItemData = ref<MediaItem | null>(null);

    // Modal actions
    const openCreateFolderModal = () => {
        showCreateModal.value = true;
    };

    const closeCreateFolderModal = () => {
        showCreateModal.value = false;
    };

    const openRenameModal = (item: MediaItem) => {
        renameItemData.value = item;
        showRenameModal.value = true;
    };

    const closeRenameModal = () => {
        showRenameModal.value = false;
        renameItemData.value = null;
    };

    const openDeleteModal = (item: MediaItem | null = null) => {
        deleteItemData.value = item;
        showDeleteModal.value = true;
    };

    const closeDeleteModal = () => {
        showDeleteModal.value = false;
        deleteItemData.value = null;
    };

    const openUploadModal = () => {
        showUploadModal.value = true;
    };

    const closeUploadModal = () => {
        showUploadModal.value = false;
    };

    return {
        // State
        showCreateModal,
        showRenameModal,
        showDeleteModal,
        showUploadModal,
        renameItemData,
        deleteItemData,

        // Modal actions
        openCreateFolderModal,
        closeCreateFolderModal,
        openRenameModal,
        closeRenameModal,
        openDeleteModal,
        closeDeleteModal,
        openUploadModal,
        closeUploadModal,
    };
}
