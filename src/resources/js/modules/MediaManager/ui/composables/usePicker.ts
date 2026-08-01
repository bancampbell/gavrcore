import { ref } from 'vue';
import type { MediaItem } from '../types';

export interface PickedFile {
    url: string;
    name: string;
    path: string;
}

export function usePicker() {
    const selectedFileForPicker = ref<PickedFile | null>(null);

    const pickFile = (item: MediaItem): PickedFile => {
        const fileData: PickedFile = {
            url: `/storage/uploads/${item.path}`,
            name: item.name,
            path: item.path,
        };
        selectedFileForPicker.value = fileData;
        return fileData;
    };

    const unpickFile = () => {
        selectedFileForPicker.value = null;
    };

    const isPicked = (path: string) => selectedFileForPicker.value?.path === path;

    const togglePick = (item: MediaItem, isCurrentlySelected: boolean): PickedFile | null => {
        if (isCurrentlySelected) {
            unpickFile();
            return null;
        }
        return pickFile(item);
    };

    return {
        selectedFileForPicker,
        pickFile,
        unpickFile,
        isPicked,
        togglePick,
    };
}
