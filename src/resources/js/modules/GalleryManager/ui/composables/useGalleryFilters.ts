import { ref, computed, type Ref, type ComputedRef } from 'vue';
import type { GalleryListItem, GalleryFilters } from '../types/index';

export function useGalleryFilters(galleries: Ref<GalleryListItem[]> | ComputedRef<GalleryListItem[]>) {
    const filters = ref<GalleryFilters>({
        search: '',
        type: '',
        status: '',
    });

    const filteredGalleries = computed(() => {
        let items = [...galleries.value];

        if (filters.value.search) {
            const q = filters.value.search.toLowerCase();
            items = items.filter(g => g.title.toLowerCase().includes(q));
        }

        if (filters.value.type) {
            items = items.filter(g => g.type === filters.value.type);
        }

        if (filters.value.status !== '') {
            items = items.filter(g => g.status === (filters.value.status === '1'));
        }

        return items;
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
        filteredGalleries,
        resetFilters,
    };
}
