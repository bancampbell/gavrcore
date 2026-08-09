import { ref, computed } from 'vue';
import type { MediaItem } from '../types';
import type { useMediaActions } from './useMediaActions';

export function useContents(
    actions: ReturnType<typeof useMediaActions>,
    acceptedFiles?: string[],
    mode?: 'full' | 'picker',
) {
    const allFolders = ref<MediaItem[]>([]);
    const contents = ref<MediaItem[]>([]);
    const paginatedData = ref<{
        data: MediaItem[];
        total: number;
        page: number;
        per_page: number;
        last_page: number;
    } | null>(null);
    const loading = ref(false);
    const foldersLoading = ref(false);
    const searchQuery = ref('');
    const showSearch = ref(false);
    const sortOrder = ref<'asc' | 'desc'>('asc');

    const folders = computed(() => contents.value.filter(i => i.type === 'folder'));
    const files = computed(() => contents.value.filter(i => i.type === 'file'));

    const filteredFiles = computed(() => {
        let result = files.value;
        if (mode === 'picker' && acceptedFiles && acceptedFiles.length > 0) {
            result = result.filter(file => {
                const ext = file.name.split('.').pop()?.toLowerCase() || '';
                return acceptedFiles.includes(ext);
            });
        }
        return result;
    });

    const sortedFilteredFolders = computed(() => folders.value);
    const sortedFilteredFiles = computed(() => filteredFiles.value);

    const foldersCount = computed(() => folders.value.length);
    const filesCount = computed(() => files.value.length);
    const totalCount = computed(() => paginatedData.value?.total ?? contents.value.length);

    const loadFolders = async () => {
        foldersLoading.value = true;
        try {
            allFolders.value = await actions.loadFolders();
        } finally {
            foldersLoading.value = false;
        }
    };

    const load = async (path: string, page: number = 1) => {
        loading.value = true;
        try {
            const result = await actions.loadPaginatedContents(
                path,
                page,
                20,
                sortOrder.value === 'asc' ? 'name_asc' : 'name_desc',
                searchQuery.value || null,
            );
            paginatedData.value = result;
            contents.value = result.data || [];
        } finally {
            loading.value = false;
        }
    };

    const setSortOrder = (order: 'asc' | 'desc') => {
        sortOrder.value = order;
    };

    const openSearch = () => {
        showSearch.value = true;
    };

    const clearSearch = () => {
        showSearch.value = false;
        searchQuery.value = '';
    };

    return {
        allFolders,
        contents,
        paginatedData,
        loading,
        foldersLoading,
        searchQuery,
        showSearch,
        sortOrder,
        sortedFilteredFolders,
        sortedFilteredFiles,
        foldersCount,
        filesCount,
        totalCount,
        load,
        loadFolders,
        setSortOrder,
        openSearch,
        clearSearch,
    };
}
