// resources/js/Pages/Admin/MediaManager/composables/useSearch.ts

import { ref } from 'vue';

export function useSearch() {
    const showSearch = ref(false);
    const searchQuery = ref('');

    const clearSearch = () => {
        showSearch.value = false;
        searchQuery.value = '';
    };

    const openSearch = () => {
        showSearch.value = true;
    };

    const filterItems = (items: any[], query: string) => {
        if (!query) return items;
        const lowerQuery = query.toLowerCase();
        return items.filter(item => item.name.toLowerCase().includes(lowerQuery));
    };

    return {
        showSearch,
        searchQuery,
        clearSearch,
        openSearch,
        filterItems
    };
}
