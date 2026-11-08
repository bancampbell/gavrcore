import { describe, it, expect, vi, beforeEach } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import type { VueWrapper } from '@vue/test-utils';
import { useGalleries } from '@/modules/GalleryManager/ui/composables/useGalleries';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { GalleryListItem, GalleryListResponse } from '@/modules/GalleryManager/ui/types';

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

function paginatorResponse(items: GalleryListItem[], overrides: Partial<GalleryListResponse> = {}): GalleryListResponse {
    return {
        data: items,
        from: items.length > 0 ? 1 : null,
        to: items.length,
        total: items.length,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        ...overrides,
    };
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
        const [{ galleries, pagination, loading, error }] = withSetup(useGalleries);

        expect(galleries.value).toEqual([]);
        expect(pagination.value).toEqual({
            from: 0,
            to: 0,
            total: 0,
            per_page: 10,
            current_page: 1,
            last_page: 1,
        });
        expect(loading.value).toBe(false);
        expect(error.value).toBeNull();
    });

    it('loads galleries and pagination meta from paginator response', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({
            data: paginatorResponse(mockGalleries, { from: 11, to: 12, total: 25, current_page: 2, last_page: 3 }),
        } as any);

        const [{ galleries, pagination, loading, error, loadGalleries }] = withSetup(useGalleries);
        await loadGalleries({ page: 2 });
        await nextTick();

        expect(loading.value).toBe(false);
        expect(galleries.value).toEqual(mockGalleries);
        expect(pagination.value).toEqual({
            from: 11,
            to: 12,
            total: 25,
            per_page: 10,
            current_page: 2,
            last_page: 3,
        });
        expect(error.value).toBeNull();
        expect(galleryApi.list).toHaveBeenCalledWith({ page: 2 });
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

    it('passes filters and pagination params to api when provided', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: paginatorResponse([]) } as any);

        const [{ loadGalleries }] = withSetup(useGalleries);
        await loadGalleries({ search: 'grid', type: 'grid', status: true, page: 3, per_page: 25 });

        expect(galleryApi.list).toHaveBeenCalledWith({
            search: 'grid',
            type: 'grid',
            status: true,
            page: 3,
            per_page: 25,
        });
    });
});
