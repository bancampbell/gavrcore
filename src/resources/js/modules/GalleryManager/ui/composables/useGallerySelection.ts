import { ref, computed, type Ref, type ComputedRef } from 'vue';
import type { GalleryListItem } from '../types/index';

export function useGallerySelection(items: Ref<GalleryListItem[]> | ComputedRef<GalleryListItem[]>) {
    const selectedIds = ref<number[]>([]);

    const allSelected = computed({
        get: () => items.value.length > 0 && selectedIds.value.length === items.value.length,
        set: (value: boolean) => {
            selectedIds.value = value ? items.value.map(g => g.id) : [];
        }
    });

    const toggleSelect = (id: number) => {
        const index = selectedIds.value.indexOf(id);
        if (index === -1) {
            selectedIds.value.push(id);
        } else {
            selectedIds.value.splice(index, 1);
        }
    };

    const clearSelection = () => {
        selectedIds.value = [];
    };

    return {
        selectedIds,
        allSelected,
        toggleSelect,
        clearSelection,
    };
}
