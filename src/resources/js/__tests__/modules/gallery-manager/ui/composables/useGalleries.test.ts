import { describe, it, expect, vi, beforeEach } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import type { VueWrapper } from '@vue/test-utils';
import { useGalleries } from '@/modules/GalleryManager/ui/composables/useGalleries';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { GalleryListItem, GalleryFilters } from '@/modules/GalleryManager/ui/types';

vi.mock('@/modules/GalleryManager/infrastructure/api/gallery-api');

function withSetup<T>(composable: () => T): [T, VueWrapper] {
    let result!: T;
    const wrapper = mount({
        setup() {
            result = composable();
            return {};
        },
        template: '<div></div>',
    });
    return [result, wrapper];
}

describe('useGalleries', () => {
    const mockGalleries: GalleryListItem[] = [
        { id: 1, title: 'Grid Gallery', type: 'grid', status: true, images_count: 5, created_at: '2024-01-01' },
        { id: 2, title: 'Slider Gallery', type: 'slider', status: false, images_count: 3, created_at: '2024-01-02' },
    ];

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('initializes with default state', () => {
        const [{ galleries, loading, error }] = withSetup(useGalleries);

        expect(galleries.value).toEqual([]);
        expect(loading.value).toBe(false);
        expect(error.value).toBeNull();
    });

    it('loads galleries successfully', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: mockGalleries } as any);

        const [{ galleries, loading, error, loadGalleries }] = withSetup(useGalleries);
        await loadGalleries();
        await nextTick();

        expect(loading.value).toBe(false);
        expect(galleries.value).toEqual(mockGalleries);
        expect(error.value).toBeNull();
        expect(galleryApi.list).toHaveBeenCalledTimes(1);
    });

    it('sets loading state during request', async () => {
        vi.mocked(galleryApi.list).mockImplementationOnce(() => new Promise(() => {}));

        const [{ loading, loadGalleries }] = withSetup(useGalleries);
        loadGalleries();
        await nextTick();

        expect(loading.value).toBe(true);
    });

    it('handles network error', async () => {
        vi.mocked(galleryApi.list).mockRejectedValueOnce(new Error('Network failure'));

        const [{ galleries, loading, error, loadGalleries }] = withSetup(useGalleries);
        await loadGalleries();
        await nextTick();

        expect(loading.value).toBe(false);
        expect(error.value).toBe('Ошибка загрузки галерей');
        expect(galleries.value).toEqual([]);
    });

    it('passes filters to api when provided', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: [] } as any);

        const [{ loadGalleries }] = withSetup(useGalleries);
        const filters: GalleryFilters = { search: 'grid', type: 'grid', status: '1' };
        await loadGalleries(filters);

        expect(galleryApi.list).toHaveBeenCalledWith({
            search: 'grid',
            type: 'grid',
            status: true,
        });
    });

    it('does not pass status when filter is empty string', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: [] } as any);

        const [{ loadGalleries }] = withSetup(useGalleries);
        await loadGalleries({ search: '', type: '', status: '' });

        expect(galleryApi.list).toHaveBeenCalledWith({ search: '', type: '', status: undefined });
    });
});
