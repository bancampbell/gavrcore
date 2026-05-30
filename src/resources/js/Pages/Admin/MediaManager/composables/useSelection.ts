// resources/js/Pages/Admin/MediaManager/composables/useSelection.ts

import { ref } from 'vue';

export interface MediaItem {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

export function useSelection() {
    const selectedItems = ref<string[]>([]);
    const selectedItem = ref<MediaItem | null>(null);

    const toggleSelect = (path: string, item: MediaItem) => {
        const index = selectedItems.value.indexOf(path);
        if (index === -1) {
            selectedItems.value.push(path);
            selectedItem.value = item;
        } else {
            selectedItems.value.splice(index, 1);
            if (selectedItem.value?.path === path) {
                selectedItem.value = null;
            }
        }
    };

    const selectItem = (item: MediaItem) => {
        selectedItem.value = item;
        if (!selectedItems.value.includes(item.path)) {
            selectedItems.value.push(item.path);
        }
    };

    const clearSelection = () => {
        selectedItems.value = [];
        selectedItem.value = null;
    };

    const isSelected = (path: string) => {
        return selectedItems.value.includes(path);
    };

    return {
        selectedItems,
        selectedItem,
        toggleSelect,
        selectItem,
        clearSelection,
        isSelected
    };
}
