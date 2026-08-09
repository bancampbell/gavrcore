import { ref, watch, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import type { MaterialFilters, MaterialsData } from '../types';

export function useMaterialFilters(props: {
    materials: MaterialsData;
    filters?: MaterialFilters;
    perPage?: number;
}, baseUrl: string = '/admin/materials') {
    const filters = ref<MaterialFilters>({
        search: props.filters?.search || '',
        state: props.filters?.state || '',
        category_id: props.filters?.category_id ? Number(props.filters.category_id) : null,
        author: props.filters?.author ? Number(props.filters.author) : null,
        sort: props.filters?.sort || 'id',
        direction: props.filters?.direction || 'desc',
    });

    const perPage = ref<number>(props.perPage || 10);
    let searchTimeout: number | null = null;

    watch(
        () => props.filters,
        (newFilters) => {
            if (newFilters) {
                filters.value.search = newFilters.search || '';
                filters.value.state = newFilters.state || '';
                filters.value.category_id = newFilters.category_id ? Number(newFilters.category_id) : null;
                filters.value.author = newFilters.author ? Number(newFilters.author) : null;
                filters.value.sort = newFilters.sort || 'id';
                filters.value.direction = newFilters.direction || 'desc';
            }
        },
        { deep: true }
    );

    const applyFilters = (page?: number) => {
        const safePage = typeof page === 'number' ? page : 1;

        const params: Record<string, unknown> = {
            search: filters.value.search,
            state: filters.value.state,
            category_id: filters.value.category_id ?? null,
            author: filters.value.author ?? null,
            per_page: perPage.value,
            sort: filters.value.sort,
            direction: filters.value.direction,
            page: safePage,
        };

        router.get(baseUrl, params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const debounceSearch = () => {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = window.setTimeout(() => {
            applyFilters(1);
        }, 300);
    };

    const resetFilters = () => {
        filters.value = {
            search: '',
            state: '',
            category_id: null,
            author: null,
            sort: 'id',
            direction: 'desc',
        };
        applyFilters(1);
    };

    const changePerPage = () => {
        applyFilters(1);
    };

    const prevPage = () => {
        if (props.materials.current_page > 1) {
            applyFilters(props.materials.current_page - 1);
        }
    };

    const nextPage = () => {
        if (props.materials.current_page < props.materials.last_page) {
            applyFilters(props.materials.current_page + 1);
        }
    };

    const toggleSort = (column: string) => {
        if (filters.value.sort === column) {
            filters.value.direction = filters.value.direction === 'asc' ? 'desc' : 'asc';
        } else {
            filters.value.sort = column;
            filters.value.direction = 'asc';
        }
        applyFilters(1);
    };

    onBeforeUnmount(() => {
        if (searchTimeout) clearTimeout(searchTimeout);
    });

    return {
        filters,
        perPage,
        applyFilters,
        debounceSearch,
        resetFilters,
        changePerPage,
        prevPage,
        nextPage,
        toggleSort,
    };
}
