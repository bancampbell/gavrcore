// resources/js/Pages/Admin/MediaManager/composables/useSorting.ts

import { ref } from 'vue';

export type SortOrder = 'asc' | 'desc';

export function useSorting() {
    const sortOrder = ref<SortOrder>('asc');

    const setSortOrder = (order: SortOrder) => {
        sortOrder.value = order;
    };

    const toggleSort = () => {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    };

    const sortItems = (items: any[]) => {
        const sorted = [...items];
        if (sortOrder.value === 'asc') {
            return sorted.sort((a, b) => a.name.localeCompare(b.name));
        } else {
            return sorted.sort((a, b) => b.name.localeCompare(a.name));
        }
    };

    const isAscActive = () => sortOrder.value === 'asc';
    const isDescActive = () => sortOrder.value === 'desc';

    return {
        sortOrder,
        setSortOrder,
        toggleSort,
        sortItems,
        isAscActive,
        isDescActive
    };
}
