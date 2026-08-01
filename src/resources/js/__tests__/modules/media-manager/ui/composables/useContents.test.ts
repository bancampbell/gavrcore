import { describe, it, expect, vi } from 'vitest';
import { ref } from 'vue';
import { useContents } from '@/modules/media-manager/ui/composables/useContents';

const createActions = (overrides: object = {}) => ({
    loading: ref(false),
    loadContents: vi.fn(),
    loadPaginatedContents: vi.fn(),
    loadFolders: vi.fn(),
    createFolder: vi.fn(),
    renameItem: vi.fn(),
    deleteItem: vi.fn(),
    deleteItems: vi.fn(),
    copyItem: vi.fn(),
    uploadFile: vi.fn(),
    ...overrides,
});

describe('useContents', () => {
    it('initializes empty', () => {
        const contents = useContents(createActions());
        expect(contents.contents.value).toEqual([]);
        expect(contents.loading.value).toBe(false);
    });

    it('loads folders separately', async () => {
        const actions = createActions({
            loadFolders: vi.fn().mockResolvedValue([
                { name: 'folder1', path: 'folder1', type: 'folder' },
            ]),
        });
        const contents = useContents(actions);
        await contents.loadFolders();
        expect(actions.loadFolders).toHaveBeenCalled();
        expect(contents.allFolders.value.length).toBe(1);
    });

    it('loads contents', async () => {
        const actions = createActions({
            loadPaginatedContents: vi.fn().mockResolvedValue({
                data: [
                    { name: 'file1.jpg', path: 'file1.jpg', type: 'file' },
                    { name: 'folder1', path: 'folder1', type: 'folder' },
                ],
                total: 2,
                page: 1,
                per_page: 20,
                last_page: 1,
            }),
        });
        const contents = useContents(actions);
        await contents.load('');
        expect(actions.loadPaginatedContents).toHaveBeenCalledWith('', 1, 20, 'name_asc', null);
        expect(contents.foldersCount.value).toBe(1);
        expect(contents.filesCount.value).toBe(1);
    });

    it('filters files by accepted extensions in picker mode', async () => {
        const actions = createActions({
            loadFolders: vi.fn().mockResolvedValue([]),
            loadPaginatedContents: vi.fn().mockResolvedValue({
                data: [
                    { name: 'file1.jpg', path: 'file1.jpg', type: 'file' },
                    { name: 'file2.exe', path: 'file2.exe', type: 'file' },
                ],
                total: 2,
                page: 1,
                per_page: 20,
                last_page: 1,
            }),
        });
        const contents = useContents(actions, ['jpg'], 'picker');
        await contents.load('');
        expect(contents.sortedFilteredFiles.value).toHaveLength(1);
        expect(contents.sortedFilteredFiles.value[0].name).toBe('file1.jpg');
    });

    it('passes sort order to API', async () => {
        const actions = createActions({
            loadFolders: vi.fn().mockResolvedValue([]),
            loadPaginatedContents: vi.fn().mockResolvedValue({
                data: [
                    { name: 'b.jpg', path: 'b.jpg', type: 'file' },
                    { name: 'a.jpg', path: 'a.jpg', type: 'file' },
                ],
                total: 2,
                page: 1,
                per_page: 20,
                last_page: 1,
            }),
        });
        const contents = useContents(actions);
        await contents.load('');
        expect(actions.loadPaginatedContents).toHaveBeenCalledWith('', 1, 20, 'name_asc', null);

        contents.setSortOrder('desc');
        await contents.load('');
        expect(actions.loadPaginatedContents).toHaveBeenLastCalledWith('', 1, 20, 'name_desc', null);
    });

    it('passes search query to API on load', async () => {
        const actions = createActions({
            loadFolders: vi.fn().mockResolvedValue([]),
            loadPaginatedContents: vi.fn().mockResolvedValue({
                data: [
                    { name: 'cat.jpg', path: 'cat.jpg', type: 'file' },
                    { name: 'dog.jpg', path: 'dog.jpg', type: 'file' },
                ],
                total: 2,
                page: 1,
                per_page: 20,
                last_page: 1,
            }),
        });
        const contents = useContents(actions);
        contents.searchQuery.value = 'cat';
        await contents.load('');
        expect(actions.loadPaginatedContents).toHaveBeenCalledWith('', 1, 20, 'name_asc', 'cat');
        expect(contents.sortedFilteredFiles.value).toHaveLength(2);
    });

    it('clears search', async () => {
        const actions = createActions({
            loadFolders: vi.fn().mockResolvedValue([]),
            loadPaginatedContents: vi.fn().mockResolvedValue({
                data: [],
                total: 0,
                page: 1,
                per_page: 20,
                last_page: 1,
            }),
        });
        const contents = useContents(actions);
        contents.searchQuery.value = 'test';
        contents.showSearch.value = true;
        contents.clearSearch();
        expect(contents.searchQuery.value).toBe('');
        expect(contents.showSearch.value).toBe(false);
    });
});
