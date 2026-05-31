// resources/js/Pages/Admin/MediaManager/composables/useMediaManager.ts

import { ref, computed } from 'vue';
import { useFileOperations } from './useFileOperations';
import { useSearch } from './useSearch';
import { useSorting } from './useSorting';
import { useSelection } from './useSelection';
import type { MediaItem } from './useSelection';

export function useMediaManager(mode: 'full' | 'picker' = 'full', acceptedFiles?: string[]) {
    // State
    const allFolders = ref<MediaItem[]>([]);
    const contents = ref<MediaItem[]>([]);
    const currentPath = ref('');
    const showCreateModal = ref(false);
    const showRenameModal = ref(false);
    const showDeleteModal = ref(false);
    const showUploadModal = ref(false);
    const renameItemData = ref<MediaItem | null>(null);
    const deleteItemData = ref<MediaItem | null>(null);
    const newItemName = ref('');
    const newFolderName = ref('');
    const uploadFileInput = ref<HTMLInputElement | null>(null);

    const selectedFileForPicker = ref<{ url: string; name: string; path: string } | null>(null);

    const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });
    let notificationTimeout: number | null = null;

    const showNotification = (message: string, type: 'success' | 'error' = 'success') => {
        if (notificationTimeout) clearTimeout(notificationTimeout);
        notification.value = { show: true, message, type };
        notificationTimeout = window.setTimeout(() => {
            notification.value.show = false;
        }, 3000);
    };

    const {
        loadingFolders,
        loadingContents,
        uploadLoading,
        uploadFiles,
        loadFolders,
        loadContents,
        createFolder,
        renameItem,
        deleteItem,
        copyItem,
        uploadFile,
        setUploadFiles
    } = useFileOperations(showNotification);

    const {
        showSearch,
        searchQuery,
        clearSearch,
        openSearch,
        filterItems
    } = useSearch();

    const {
        sortOrder,
        setSortOrder,
        sortItems,
        isAscActive,
        isDescActive
    } = useSorting();

    const {
        selectedItems,
        selectedItem,
        toggleSelect,
        selectItem,
        clearSelection
    } = useSelection();

    const rootFolders = computed(() => allFolders.value.filter(f => f && f.path && !f.path.includes('/')));
    const folders = computed(() => contents.value.filter(i => i && i.type === 'folder'));

    const files = computed(() => {
        let allFiles = contents.value.filter(i => i && i.type === 'file');

        if (acceptedFiles && acceptedFiles.length > 0 && mode === 'picker') {
            allFiles = allFiles.filter(file => {
                const extension = file.name.split('.').pop()?.toLowerCase() || '';
                return acceptedFiles.includes(extension);
            });
        }

        return allFiles;
    });

    const filteredFolders = computed(() => {
        const filtered = filterItems(folders.value, searchQuery.value);
        return filtered.filter(f => f && f.name);
    });

    const filteredFiles = computed(() => {
        const filtered = filterItems(files.value, searchQuery.value);
        return filtered.filter(f => f && f.name);
    });

    const sortedFilteredFolders = computed(() => {
        const sorted = sortItems(filteredFolders.value);
        return sorted.filter(f => f && f.name);
    });

    const sortedFilteredFiles = computed(() => {
        const sorted = sortItems(filteredFiles.value);
        return sorted.filter(f => f && f.name);
    });

    const filteredContents = computed(() => [...filteredFolders.value, ...filteredFiles.value]);

    const currentPathDisplay = computed(() => currentPath.value || '/');
    const foldersCount = computed(() => folders.value.length);
    const filesCount = computed(() => files.value.length);
    const canGoBack = computed(() => currentPath.value !== '');

    const loadData = async () => {
        allFolders.value = await loadFolders();
        contents.value = await loadContents(currentPath.value);
        clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const navigateToFolder = async (path: string) => {
        currentPath.value = path;
        contents.value = await loadContents(path);
        clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const goBack = async () => {
        if (!currentPath.value) return;
        const parts = currentPath.value.split('/');
        parts.pop();
        currentPath.value = parts.join('');
        contents.value = await loadContents(currentPath.value);
        clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const goHome = async () => {
        currentPath.value = '';
        contents.value = await loadContents('');
        clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const openCreateFolderModal = () => {
        newFolderName.value = '';
        showCreateModal.value = true;
    };

    const handleCreateFolder = async (folderName: string) => {
        if (!folderName.trim()) return;
        const success = await createFolder(folderName, currentPath.value);
        if (success) {
            showCreateModal.value = false;
            await loadData();
        }
    };

    const openRenameModal = () => {
        if (!selectedItem.value) return;
        renameItemData.value = selectedItem.value;
        newItemName.value = selectedItem.value.name;
        showRenameModal.value = true;
    };

    const handleRename = async (newName: string) => {
        if (!renameItemData.value || !newName.trim()) return;

        if (newName === renameItemData.value.name) {
            showNotification('Имя не изменено', 'error');
            showRenameModal.value = false;
            renameItemData.value = null;
            newItemName.value = '';
            return;
        }

        const success = await renameItem(renameItemData.value.path, newName);
        if (success) {
            showRenameModal.value = false;
            renameItemData.value = null;
            newItemName.value = '';
            await loadData();
        }
    };

    const openDeleteModal = () => {
        if (!selectedItem.value) return;
        deleteItemData.value = selectedItem.value;
        showDeleteModal.value = true;
    };

    const handleDelete = async () => {
        if (!deleteItemData.value) return;
        const success = await deleteItem(deleteItemData.value.path);
        if (success) {
            showDeleteModal.value = false;
            deleteItemData.value = null;
            await loadData();
        }
    };

    const handleCopy = async () => {
        if (!selectedItem.value) return;
        const success = await copyItem(selectedItem.value.path);
        if (success) {
            await loadData();
        }
    };

    const openUploadModal = () => {
        showUploadModal.value = true;
    };

    const handleUpload = async (files: FileList) => {
        if (!files || files.length === 0) return;
        const success = await uploadFile(files, currentPath.value);
        if (success) {
            showUploadModal.value = false;
            setUploadFiles(null);
            await loadData();
        }
    };

    const handleFileSelect = (event: Event) => {
        const target = event.target as HTMLInputElement;
        setUploadFiles(target.files);
    };

    const selectFolder = (item: MediaItem) => {
        if (!item) return;
        if (!showSearch.value) {
            if (mode === 'picker') {
                navigateToFolder(item.path);
            } else {
                selectItem(item);
                navigateToFolder(item.path);
            }
        } else {
            selectItem(item);
        }
    };

    const selectFileItem = (item: MediaItem) => {
        if (!item) return;
        if (mode === 'picker') {
            selectedFileForPicker.value = {
                url: `/storage/uploads/${item.path}`,
                name: item.name,
                path: item.path
            };
            selectItem(item);
        } else {
            selectItem(item);
        }
    };

    const getSelectedFile = () => {
        return selectedFileForPicker.value;
    };

    const clearSelectedFile = () => {
        selectedFileForPicker.value = null;
        clearSelection();
    };

    return {
        allFolders,
        contents,
        currentPath,
        showCreateModal,
        showRenameModal,
        showDeleteModal,
        showUploadModal,
        showSearch,
        searchQuery,
        sortOrder,
        selectedItems,
        selectedItem,
        renameItemData,
        deleteItemData,
        newItemName,
        newFolderName,
        uploadFiles,
        uploadLoading,
        loadingFolders,
        loadingContents,
        uploadFileInput,
        notification,
        selectedFileForPicker,
        rootFolders,
        sortedFilteredFolders,
        sortedFilteredFiles,
        filteredContents,
        currentPathDisplay,
        foldersCount,
        filesCount,
        canGoBack,
        isAscActive,
        isDescActive,
        loadData,
        navigateToFolder,
        goBack,
        goHome,
        openCreateFolderModal,
        handleCreateFolder,
        openRenameModal,
        handleRename,
        openDeleteModal,
        handleDelete,
        handleCopy,
        openUploadModal,
        handleUpload,
        handleFileSelect,
        clearSearch,
        openSearch,
        setSortOrder,
        toggleSelect,
        selectFolder,
        selectFileItem,
        getSelectedFile,
        clearSelectedFile
    };
}
