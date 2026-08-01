import { describe, it, expect } from 'vitest';
import { useModals } from '@/modules/MediaManager/ui/composables/useModals';
import type { MediaItem } from '@/modules/MediaManager/ui/types';

const mockItem: MediaItem = {
    name: 'test',
    path: 'test',
    type: 'file',
};

describe('useModals', () => {
    it('initializes closed', () => {
        const { showCreateModal, showRenameModal, showDeleteModal, showUploadModal } = useModals();
        expect(showCreateModal.value).toBe(false);
        expect(showRenameModal.value).toBe(false);
        expect(showDeleteModal.value).toBe(false);
        expect(showUploadModal.value).toBe(false);
    });

    it('opens and closes create modal', () => {
        const { showCreateModal, openCreateFolderModal, closeCreateFolderModal } = useModals();
        openCreateFolderModal();
        expect(showCreateModal.value).toBe(true);
        closeCreateFolderModal();
        expect(showCreateModal.value).toBe(false);
    });

    it('opens rename modal with item', () => {
        const { showRenameModal, renameItemData, openRenameModal, closeRenameModal } = useModals();
        openRenameModal(mockItem);
        expect(showRenameModal.value).toBe(true);
        expect(renameItemData.value).toEqual(mockItem);
        closeRenameModal();
        expect(showRenameModal.value).toBe(false);
        expect(renameItemData.value).toBeNull();
    });

    it('opens delete modal with item', () => {
        const { showDeleteModal, deleteItemData, openDeleteModal, closeDeleteModal } = useModals();
        openDeleteModal(mockItem);
        expect(showDeleteModal.value).toBe(true);
        expect(deleteItemData.value).toEqual(mockItem);
        closeDeleteModal();
        expect(showDeleteModal.value).toBe(false);
        expect(deleteItemData.value).toBeNull();
    });

    it('opens delete modal without item (batch)', () => {
        const { showDeleteModal, deleteItemData, openDeleteModal } = useModals();
        openDeleteModal(null);
        expect(showDeleteModal.value).toBe(true);
        expect(deleteItemData.value).toBeNull();
    });
});
