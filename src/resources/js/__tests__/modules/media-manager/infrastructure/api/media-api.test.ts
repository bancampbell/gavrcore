import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mediaApi } from '@/modules/MediaManager/infrastructure/api/media-api';

describe('mediaApi', () => {
    let fetchSpy: any;

    beforeEach(() => {
        vi.restoreAllMocks();
        fetchSpy = vi.spyOn(global, 'fetch').mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({ data: [] }),
        } as Response);

        document.head.innerHTML = '<meta name="csrf-token" content="test-token">';
    });

    it('includes CSRF token in headers', async () => {
        await mediaApi.loadFolders();
        const call = fetchSpy.mock.calls[0];
        expect(call[1].headers['X-CSRF-TOKEN']).toBe('test-token');
    });

    it('loadContents sends correct path', async () => {
        await mediaApi.loadContents('test/path');
        expect(fetchSpy).toHaveBeenCalledWith(
            expect.stringContaining('/admin/media/contents?path=test%2Fpath'),
            expect.objectContaining({ headers: expect.any(Object) })
        );
    });

    it('loadPaginatedContents sends params', async () => {
        await mediaApi.loadPaginatedContents('path', 2, 50, 'name_desc', 'query');
        const url = fetchSpy.mock.calls[0][0] as string;
        expect(url).toContain('page=2');
        expect(url).toContain('per_page=50');
        expect(url).toContain('sort=name_desc');
        expect(url).toContain('search=query');
    });

    it('createFolder sends POST with JSON', async () => {
        await mediaApi.createFolder('folder', 'parent');
        const [, opts] = fetchSpy.mock.calls[0];
        expect(opts.method).toBe('POST');
        expect(opts.headers['Content-Type']).toBe('application/json');
        expect(JSON.parse(opts.body)).toEqual({ name: 'folder', path: 'parent' });
    });

    it('renameItem sends POST with JSON', async () => {
        await mediaApi.renameItem('old', 'new');
        const [, opts] = fetchSpy.mock.calls[0];
        expect(opts.method).toBe('POST');
        expect(JSON.parse(opts.body)).toEqual({ old_path: 'old', new_name: 'new' });
    });

    it('deleteItem sends DELETE with JSON', async () => {
        await mediaApi.deleteItem('path');
        const [, opts] = fetchSpy.mock.calls[0];
        expect(opts.method).toBe('DELETE');
        expect(JSON.parse(opts.body)).toEqual({ path: 'path' });
    });

    it('deleteItems sends DELETE with array', async () => {
        await mediaApi.deleteItems(['a', 'b']);
        const [, opts] = fetchSpy.mock.calls[0];
        expect(JSON.parse(opts.body)).toEqual({ paths: ['a', 'b'] });
    });

    it('copyItem sends POST', async () => {
        await mediaApi.copyItem('path');
        const [, opts] = fetchSpy.mock.calls[0];
        expect(opts.method).toBe('POST');
    });

    it('uploadFile sends FormData without manual Content-Type', async () => {
        const file = new File(['content'], 'test.jpg', { type: 'image/jpeg' });
        const files = [file] as any;
        files.length = 1;
        files.item = (i: number) => files[i];

        await mediaApi.uploadFile(files as FileList, 'path');
        const [, opts] = fetchSpy.mock.calls[0];
        expect(opts.method).toBe('POST');
        expect(opts.body instanceof FormData).toBe(true);
        expect(opts.headers['Content-Type']).toBeUndefined();
    });

    it('throws on non-ok response', async () => {
        fetchSpy.mockResolvedValueOnce({
            ok: false,
            json: () => Promise.resolve({ message: 'Server error' }),
        } as Response);
        await expect(mediaApi.loadFolders()).rejects.toThrow('Server error');
    });
});
