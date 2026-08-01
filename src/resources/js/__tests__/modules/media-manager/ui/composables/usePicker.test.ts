import { describe, it, expect } from 'vitest';
import { usePicker } from '@/modules/media-manager/ui/composables/usePicker';
import type { MediaItem } from '@/modules/media-manager/ui/types';

const mockFile = (path: string): MediaItem => ({
    name: path,
    path,
    type: 'file',
});

describe('usePicker', () => {
    it('initializes empty', () => {
        const { selectedFileForPicker } = usePicker();
        expect(selectedFileForPicker.value).toBeNull();
    });

    it('picks file with correct url', () => {
        const { selectedFileForPicker, pickFile } = usePicker();
        const result = pickFile(mockFile('photo.jpg'));
        expect(result).toEqual({
            url: '/storage/uploads/photo.jpg',
            name: 'photo.jpg',
            path: 'photo.jpg',
        });
        expect(selectedFileForPicker.value).toEqual(result);
    });

    it('unpicks file', () => {
        const { selectedFileForPicker, pickFile, unpickFile } = usePicker();
        pickFile(mockFile('photo.jpg'));
        unpickFile();
        expect(selectedFileForPicker.value).toBeNull();
    });

    it('checks isPicked', () => {
        const { isPicked, pickFile } = usePicker();
        pickFile(mockFile('photo.jpg'));
        expect(isPicked('photo.jpg')).toBe(true);
        expect(isPicked('other.jpg')).toBe(false);
    });

    it('toggles pick on when not selected', () => {
        const { selectedFileForPicker, togglePick } = usePicker();
        const result = togglePick(mockFile('photo.jpg'), false);
        expect(result).not.toBeNull();
        expect(selectedFileForPicker.value?.path).toBe('photo.jpg');
    });

    it('toggles pick off when selected', () => {
        const { selectedFileForPicker, togglePick } = usePicker();
        const result = togglePick(mockFile('photo.jpg'), true);
        expect(result).toBeNull();
        expect(selectedFileForPicker.value).toBeNull();
    });
});
