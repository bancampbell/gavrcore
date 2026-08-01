import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useMediaActions } from '@/modules/media-manager/ui/composables/useMediaActions';

describe('useMediaActions', () => {
    beforeEach(() => {
        vi.restoreAllMocks();
    });

    const mockFetch = (body: object, ok = true) => {
        global.fetch = vi.fn().mockResolvedValue({
            ok,
            json: () => Promise.resolve(body),
        } as Response);
    };

    it('sets loading during loadFolders', async () => {
        mockFetch([{ name: 'folder', path: 'folder', type: 'folder' }]);
        const { loading, loadFolders } = useMediaActions();
        const promise = loadFolders();
        expect(loading.value).toBe(true);
        await promise;
        expect(loading.value).toBe(false);
    });

    it('returns folders on loadFolders', async () => {
        mockFetch([{ name: 'folder', path: 'folder', type: 'folder' }]);
        const { loadFolders } = useMediaActions();
        const result = await loadFolders();
        expect(result).toHaveLength(1);
        expect(result[0].name).toBe('folder');
    });

    it('returns ok on createFolder success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { createFolder } = useMediaActions();
        const result = await createFolder('test', '');
        expect(result).toEqual({ ok: true });
    });

    it('returns ok:false on createFolder failure', async () => {
        mockFetch({ message: 'error' }, false);
        const { createFolder } = useMediaActions();
        const result = await createFolder('test', '');
        expect(result).toEqual({ ok: false, error: 'error' });
    });

    it('returns ok on renameItem success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { renameItem } = useMediaActions();
        const result = await renameItem('old', 'new');
        expect(result).toEqual({ ok: true });
    });

    it('returns ok on deleteItem success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { deleteItem } = useMediaActions();
        const result = await deleteItem('path');
        expect(result).toEqual({ ok: true });
    });

    it('returns ok on deleteItems success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { deleteItems } = useMediaActions();
        const result = await deleteItems(['a', 'b']);
        expect(result).toEqual({ ok: true });
    });

    it('returns ok on copyItem success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { copyItem } = useMediaActions();
        const result = await copyItem('path');
        expect(result).toEqual({ ok: true });
    });

    it('returns ok on uploadFile success', async () => {
        mockFetch({ success: true, message: 'ok' });
        const { uploadFile } = useMediaActions();
        const files = { length: 1, 0: new File([], 'test.jpg') } as unknown as FileList;
        const result = await uploadFile(files, '');
        expect(result).toEqual({ ok: true });
    });

    it('returns ok:false on uploadFile failure', async () => {
        mockFetch({ message: 'error' }, false);
        const { uploadFile } = useMediaActions();
        const files = { length: 1, 0: new File([], 'test.jpg') } as unknown as FileList;
        const result = await uploadFile(files, '');
        expect(result).toEqual({ ok: false, error: 'error' });
    });
});
