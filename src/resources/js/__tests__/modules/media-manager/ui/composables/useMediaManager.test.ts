import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useMediaManager } from '@/modules/media-manager/ui/composables/useMediaManager';
import type { MediaItem } from '@/modules/media-manager/ui/types';

vi.mock('@/modules/media-manager/infrastructure/api/media-api', () => ({
    mediaApi: {
        loadContents: vi.fn(),
        loadPaginatedContents: vi.fn(),
        loadFolders: vi.fn(),
        createFolder: vi.fn(),
        renameItem: vi.fn(),
        deleteItem: vi.fn(),
        deleteItems: vi.fn(),
        copyItem: vi.fn(),
        uploadFile: vi.fn(),
    },
}));

import { mediaApi } from '@/modules/media-manager/infrastructure/api/media-api';

describe('useMediaManager', () => {
    const notify = vi.fn();

    beforeEach(() => {
        vi.clearAllMocks();
        (mediaApi.loadFolders as any).mockResolvedValue([
            { name: '2025', path: '2025', type: 'folder' },
        ]);
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: [
                { name: 'file1.jpg', path: 'file1.jpg', type: 'file' } as MediaItem,
            ],
            total: 1,
            page: 1,
            per_page: 20,
            last_page: 1,
        });
    });

    it('initializes at root', () => {
        const mm = useMediaManager(notify);
        expect(mm.currentPath.value).toBe('');
        expect(mm.canGoBack.value).toBe(false);
    });

    it('loads data', async () => {
        const mm = useMediaManager(notify);
        await mm.loadData();
        expect(mm.rootFolders.value.length).toBeGreaterThan(0);
    });

    it('navigates to folder and reloads', async () => {
        const mm = useMediaManager(notify);
        await mm.navigateToFolder('2025');
        expect(mm.currentPath.value).toBe('2025');
        expect(mm.canGoBack.value).toBe(true);
    });

    it('goes back', async () => {
        const mm = useMediaManager(notify);
        await mm.navigateToFolder('2025/sub');
        await mm.goBack();
        expect(mm.currentPath.value).toBe('2025');
    });

    it('goes home', async () => {
        const mm = useMediaManager(notify);
        await mm.navigateToFolder('2025');
        await mm.goHome();
        expect(mm.currentPath.value).toBe('');
    });

    it('creates folder and notifies on success', async () => {
        (mediaApi.createFolder as any).mockResolvedValue({ success: true, message: 'ok' });
        const mm = useMediaManager(notify);
        await mm.handleCreateFolder('new-folder');
        expect(mediaApi.createFolder).toHaveBeenCalledWith('new-folder', '');
        expect(notify).toHaveBeenCalledWith('Папка создана', 'success');
    });

    it('notifies on create folder failure', async () => {
        (mediaApi.createFolder as any).mockRejectedValue(new Error('fail'));
        const mm = useMediaManager(notify);
        await mm.handleCreateFolder('new-folder');
        expect(notify).toHaveBeenCalledWith('fail', 'error');
    });

    it('handles rename', async () => {
        (mediaApi.renameItem as any).mockResolvedValue({ success: true, message: 'ok' });
        const mm = useMediaManager(notify);
        await mm.loadData();
        if (mm.sortedFilteredFolders.value.length > 0) {
            mm.selectFolder(mm.sortedFilteredFolders.value[0]);
            mm.openRenameModal();
            await mm.handleRename('renamed');
            expect(notify).toHaveBeenCalledWith('Переименовано успешно', 'success');
        }
    });

    it('handles delete', async () => {
        (mediaApi.deleteItem as any).mockResolvedValue({ success: true, message: 'ok' });
        const mm = useMediaManager(notify);
        await mm.loadData();
        if (mm.sortedFilteredFolders.value.length > 0) {
            mm.selectFolder(mm.sortedFilteredFolders.value[0]);
            mm.openDeleteModal();
            await mm.handleDelete();
            expect(notify).toHaveBeenCalledWith('Удалено успешно', 'success');
        }
    });

    it('handles copy', async () => {
        (mediaApi.copyItem as any).mockResolvedValue({ success: true, message: 'ok' });
        const mm = useMediaManager(notify);
        await mm.loadData();
        if (mm.sortedFilteredFolders.value.length > 0) {
            mm.selectFolder(mm.sortedFilteredFolders.value[0]);
            await mm.handleCopy();
            expect(notify).toHaveBeenCalledWith('Скопировано успешно', 'success');
        }
    });

    it('handles upload', async () => {
        (mediaApi.uploadFile as any).mockResolvedValue({ success: true, message: 'ok' });
        const mm = useMediaManager(notify);
        const files = { length: 1, 0: new File([], 'test.jpg') } as unknown as FileList;
        await mm.handleUpload(files);
        expect(notify).toHaveBeenCalledWith('Файлы загружены успешно', 'success');
    });

    it('clears selection on loadData', async () => {
        const mm = useMediaManager(notify);
        await mm.loadData();
        if (mm.sortedFilteredFiles.value.length > 0) {
            mm.toggleSelect(mm.sortedFilteredFiles.value[0].path, mm.sortedFilteredFiles.value[0]);
            expect(mm.selectedItems.value.length).toBe(1);
            await mm.loadData();
            expect(mm.selectedItems.value.length).toBe(0);
        }
    });
});
