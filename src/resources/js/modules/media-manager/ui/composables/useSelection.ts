import { ref } from 'vue';
import { MediaItem } from '../../domain/entities/MediaItem';

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
            if (selectedItem.value?.getPathString() === path) {
                selectedItem.value = null;
            }
        }
    };

    const selectItem = (item: MediaItem) => {
        selectedItem.value = item;
        const path = item.getPathString();
        if (!selectedItems.value.includes(path)) {
            selectedItems.value.push(path);
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
        isSelected,
    };
}
