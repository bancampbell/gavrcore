import { describe, it, expect } from 'vitest';
import { useSelection } from '@/modules/MediaManager/ui/composables/useSelection';
import type { MediaItem } from '@/modules/MediaManager/ui/types';

const mockItem = (path: string, type: 'folder' | 'file' = 'file'): MediaItem => ({
    name: path,
    path,
    type,
});

describe('useSelection', () => {
    it('initializes empty', () => {
        const { selectedItems, selectedItem } = useSelection();
        expect(selectedItems.value).toEqual([]);
        expect(selectedItem.value).toBeNull();
    });

    it('toggles select', () => {
        const { selectedItems, toggleSelect } = useSelection();
        const item = mockItem('test.jpg');
        toggleSelect('test.jpg', item);
        expect(selectedItems.value).toContain('test.jpg');
        toggleSelect('test.jpg', item);
        expect(selectedItems.value).not.toContain('test.jpg');
    });

    it('selects item', () => {
        const { selectedItems, selectedItem, selectItem } = useSelection();
        const item = mockItem('test.jpg');
        selectItem(item);
        expect(selectedItems.value).toContain('test.jpg');
        expect(selectedItem.value).toEqual(item);
    });

    it('clears selection', () => {
        const { selectedItems, selectedItem, selectItem, clearSelection } = useSelection();
        selectItem(mockItem('test.jpg'));
        clearSelection();
        expect(selectedItems.value).toEqual([]);
        expect(selectedItem.value).toBeNull();
    });

    it('checks isSelected', () => {
        const { isSelected, selectItem } = useSelection();
        selectItem(mockItem('test.jpg'));
        expect(isSelected('test.jpg')).toBe(true);
        expect(isSelected('other.jpg')).toBe(false);
    });
});
