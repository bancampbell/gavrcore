import { ref } from 'vue';
import type { GalleryFilters } from '../types/index';

export function useGalleryFilters() {
    const filters = ref<GalleryFilters>({
        search: '',
        type: '',
        status: '',
    });

    const resetFilters = () => {
        filters.value = {
            search: '',
            type: '',
            status: '',
        };
    };

    return {
        filters,
        resetFilters,
    };
}
