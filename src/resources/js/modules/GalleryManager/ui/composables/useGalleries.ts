import { ref } from 'vue';
import { galleryApi } from '../../infrastructure/api/gallery-api';
import type { GalleryListItem, GalleryListParams } from '../types';

export function useGalleries() {
    const galleries = ref<GalleryListItem[]>([]);
    const pagination = ref({
        from: 0,
        to: 0,
        total: 0,
        per_page: 10,
        current_page: 1,
        last_page: 1,
    });
    const loading = ref(false);
    const error = ref<string | null>(null);

    const loadGalleries = async (params?: GalleryListParams) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await galleryApi.list(params);
            galleries.value = response.data.data;
            pagination.value = {
                from: response.data.from ?? 0,
                to: response.data.to ?? 0,
                total: response.data.total,
                per_page: response.data.per_page,
                current_page: response.data.current_page,
                last_page: response.data.last_page,
            };
        } catch (err) {
            error.value = 'Ошибка загрузки галерей';
            console.error('Error loading galleries:', err);
        } finally {
            loading.value = false;
        }
    };

    return {
        galleries,
        pagination,
        loading,
        error,
        loadGalleries,
    };
}
