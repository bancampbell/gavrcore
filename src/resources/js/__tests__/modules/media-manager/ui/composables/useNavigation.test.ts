import { describe, it, expect } from 'vitest';
import { useNavigation } from '@/modules/MediaManager/ui/composables/useNavigation';

describe('useNavigation', () => {
    it('initializes with empty path', () => {
        const { currentPath } = useNavigation();
        expect(currentPath.value).toBe('');
    });

    it('navigates to folder', () => {
        const { currentPath, navigateToFolder } = useNavigation();
        navigateToFolder('test-folder');
        expect(currentPath.value).toBe('test-folder');
    });

    it('navigates to nested folder', () => {
        const { currentPath, navigateToFolder } = useNavigation();
        navigateToFolder('parent/subfolder');
        expect(currentPath.value).toBe('parent/subfolder');
    });

    it('goes back', () => {
        const { currentPath, navigateToFolder, goBack } = useNavigation();
        navigateToFolder('parent/subfolder');
        goBack();
        expect(currentPath.value).toBe('parent');
    });

    it('does not go back when at root', () => {
        const { currentPath, goBack } = useNavigation();
        goBack();
        expect(currentPath.value).toBe('');
    });

    it('goes home', () => {
        const { currentPath, navigateToFolder, goHome } = useNavigation();
        navigateToFolder('test-folder');
        goHome();
        expect(currentPath.value).toBe('');
    });

    it('returns true for canGoBack when not root', () => {
        const { canGoBack, navigateToFolder } = useNavigation();
        navigateToFolder('test-folder');
        expect(canGoBack.value).toBe(true);
    });

    it('returns false for canGoBack when root', () => {
        const { canGoBack } = useNavigation();
        expect(canGoBack.value).toBe(false);
    });

    it('displays current path', () => {
        const { currentPathDisplay, navigateToFolder } = useNavigation();
        expect(currentPathDisplay.value).toBe('/');
        navigateToFolder('test-folder');
        expect(currentPathDisplay.value).toBe('test-folder');
    });
});
