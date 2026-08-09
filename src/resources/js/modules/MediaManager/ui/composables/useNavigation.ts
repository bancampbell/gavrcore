import { ref, computed } from 'vue';

export function useNavigation() {
    const currentPath = ref('');

    const navigateToFolder = (path: string) => {
        currentPath.value = path;
    };

    const goBack = () => {
        if (!currentPath.value) return;
        const parts = currentPath.value.split('/');
        parts.pop();
        currentPath.value = parts.join('/');
    };

    const goHome = () => {
        currentPath.value = '';
    };

    const canGoBack = computed(() => currentPath.value !== '');
    const currentPathDisplay = computed(() => currentPath.value || '/');

    return {
        currentPath,
        navigateToFolder,
        goBack,
        goHome,
        canGoBack,
        currentPathDisplay,
    };
}
