import { ref } from 'vue';
import { galleryApi } from '../../infrastructure/api/gallery-api';
import type { GalleryListItem, GalleryFilters } from '../types';

export function useGalleries() {
    const galleries = ref<GalleryListItem[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const loadGalleries = async (filters?: GalleryFilters) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await galleryApi.list(filters ? {
                search: filters.search,
                type: filters.type,
                status: filters.status !== '' ? filters.status === '1' : undefined,
            } : undefined);
            galleries.value = response.data;
        } catch (err) {
            error.value = 'Ошибка загрузки галерей';
            console.error('Error loading galleries:', err);
        } finally {
            loading.value = false;
        }
    };

    return {
        galleries,
        loading,
        error,
        loadGalleries,
    };
}
