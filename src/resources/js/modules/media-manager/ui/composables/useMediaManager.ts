import { ref, computed } from 'vue';
import { useFileOperations, type NotificationFn } from './useFileOperations';
import { useSearch } from './useSearch';
import { useSorting } from './useSorting';
import { useSelection } from './useSelection';
import { useMediaManagerUI } from './useMediaManagerUI';
import { MediaItem } from '../../domain/entities/MediaItem';
import { FilePath } from '../../domain/values/FilePath';

export function useMediaManager(
    showNotification: NotificationFn,
    mode: 'full' | 'picker' = 'full',
    acceptedFiles?: string[],
) {
    const allFolders = ref<MediaItem[]>([]);
    const contents = ref<MediaItem[]>([]);
    const currentPath = ref('');
    const selectedFileForPicker = ref<{ url: string; name: string; path: string } | null>(null);

    const ui = useMediaManagerUI();
    const fileOps = useFileOperations(showNotification);
    const search = useSearch();
    const sorting = useSorting();
    const selection = useSelection();

    const rootFolders = computed(() => allFolders.value.filter(f => f && f.path && !f.path.toString().includes('/')));
    const folders = computed(() => contents.value.filter(i => i && i.isFolder()));
    const files = computed(() => {
        let allFiles = contents.value.filter(i => i && i.isFile());
        if (acceptedFiles && acceptedFiles.length > 0 && mode === 'picker') {
            allFiles = allFiles.filter(file => acceptedFiles.includes(file.getExtension()));
        }
        return allFiles;
    });

    const filteredFolders = computed(() => {
        const filtered = search.filterItems(folders.value, search.searchQuery.value);
        return filtered.filter(f => f && f.name);
    });

    const filteredFiles = computed(() => {
        const filtered = search.filterItems(files.value, search.searchQuery.value);
        return filtered.filter(f => f && f.name);
    });

    const sortedFilteredFolders = computed(() => {
        const sorted = sorting.sortItems(filteredFolders.value);
        return sorted.filter(f => f && f.name);
    });

    const sortedFilteredFiles = computed(() => {
        const sorted = sorting.sortItems(filteredFiles.value);
        return sorted.filter(f => f && f.name);
    });

    const currentPathDisplay = computed(() => currentPath.value || '/');
    const foldersCount = computed(() => folders.value.length);
    const filesCount = computed(() => files.value.length);
    const canGoBack = computed(() => currentPath.value !== '');

    const loadData = async () => {
        allFolders.value = await fileOps.loadFolders();
        contents.value = await fileOps.loadContents(currentPath.value);
        selection.clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const navigateToFolder = async (path: string) => {
        currentPath.value = path;
        contents.value = await fileOps.loadContents(path);
        selection.clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const goBack = async () => {
        if (!currentPath.value) return;
        const path = FilePath.create(currentPath.value);
        const parent = path.getParent();
        currentPath.value = parent.toString();
        contents.value = await fileOps.loadContents(currentPath.value);
        selection.clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const goHome = async () => {
        currentPath.value = '';
        contents.value = await fileOps.loadContents('');
        selection.clearSelection();
        if (mode === 'picker') {
            selectedFileForPicker.value = null;
        }
    };

    const handleCreateFolder = async (folderName: string) => {
        if (!folderName.trim()) return;
        const success = await fileOps.createFolder(folderName, currentPath.value);
        if (success) {
            ui.closeCreateFolderModal();
            await loadData();
        }
    };

    const handleRename = async (newName: string) => {
        if (!ui.renameItemData.value || !newName.trim()) return;
        if (newName === ui.renameItemData.value.name) {
            showNotification('Имя не изменено', 'error');
            ui.closeRenameModal();
            return;
        }

        const success = await fileOps.renameItem(ui.renameItemData.value.getPathString(), newName);
        if (success) {
            ui.closeRenameModal();
            await loadData();
        }
    };

    const handleDelete = async () => {
        if (selection.selectedItems.value.length > 1) {
            const success = await fileOps.deleteItems(selection.selectedItems.value);
            if (success) {
                ui.closeDeleteModal();
                await loadData();
            }
            return;
        }

        if (!ui.deleteItemData.value) {
            showNotification('Не выбран элемент для удаления', 'error');
            return;
        }

        const success = await fileOps.deleteItem(ui.deleteItemData.value.getPathString());
        if (success) {
            ui.closeDeleteModal();
            await loadData();
        }
    };

    const handleCopy = async () => {
        if (!selection.selectedItem.value) return;
        const success = await fileOps.copyItem(selection.selectedItem.value.getPathString());
        if (success) {
            await loadData();
        }
    };

    const handleUpload = async (files: FileList) => {
        if (!files || files.length === 0) return;
        const success = await fileOps.uploadFile(files, currentPath.value);
        if (success) {
            ui.closeUploadModal();
            fileOps.setUploadFiles(null);
            await loadData();
        }
    };

    const handleFileSelect = (event: Event) => {
        const target = event.target as HTMLInputElement;
        fileOps.setUploadFiles(target.files);
    };

    const selectFolder = (item: MediaItem) => {
        if (!item) return;
        selection.selectItem(item);
        showNotification(`Выбрана папка: ${item.name}`, 'success');
    };

    const openFolder = (item: MediaItem) => {
        if (!item) return;
        navigateToFolder(item.getPathString());
    };

    const selectFileItem = (item: MediaItem) => {
        if (!item) return;
        if (mode === 'picker') {
            selectedFileForPicker.value = {
                url: item.getUrl(),
                name: item.name,
                path: item.getPathString(),
            };
            selection.selectItem(item);
        } else {
            selection.selectItem(item);
        }
    };

    const getSelectedFile = () => selectedFileForPicker.value;
    const clearSelectedFile = () => {
        selectedFileForPicker.value = null;
        selection.clearSelection();
    };

    const openDeleteModal = () => {
        if (selection.selectedItems.value.length === 0 && !selection.selectedItem.value) {
            showNotification('Ничего не выбрано для удаления', 'error');
            return;
        }

        if (selection.selectedItems.value.length > 1) {
            ui.openDeleteModal(null);
        } else if (selection.selectedItem.value) {
            ui.openDeleteModal(selection.selectedItem.value as MediaItem);
        }
    };

    const openRenameModal = () => {
        if (!selection.selectedItem.value) {
            showNotification('Ничего не выбрано для переименования', 'error');
            return;
        }
        ui.openRenameModal(selection.selectedItem.value as MediaItem);
    };

    return {
        allFolders,
        contents,
        currentPath,
        selectedFileForPicker,
        showCreateModal: ui.showCreateModal,
        showRenameModal: ui.showRenameModal,
        showDeleteModal: ui.showDeleteModal,
        showUploadModal: ui.showUploadModal,
        renameItemData: ui.renameItemData,
        deleteItemData: ui.deleteItemData,
        showSearch: search.showSearch,
        searchQuery: search.searchQuery,
        sortOrder: sorting.sortOrder,
        selectedItems: selection.selectedItems,
        selectedItem: selection.selectedItem,
        uploadFiles: fileOps.uploadFiles,
        uploadLoading: fileOps.uploadLoading,
        loadingFolders: fileOps.loadingFolders,
        loadingContents: fileOps.loadingContents,
        rootFolders,
        sortedFilteredFolders,
        sortedFilteredFiles,
        currentPathDisplay,
        foldersCount,
        filesCount,
        canGoBack,
        isAscActive: sorting.isAscActive,
        isDescActive: sorting.isDescActive,
        loadData,
        navigateToFolder,
        goBack,
        goHome,
        openCreateFolderModal: ui.openCreateFolderModal,
        handleCreateFolder,
        openRenameModal,
        handleRename,
        openDeleteModal,
        handleDelete,
        handleCopy,
        openUploadModal: ui.openUploadModal,
        handleUpload,
        handleFileSelect,
        clearSearch: search.clearSearch,
        openSearch: search.openSearch,
        setSortOrder: sorting.setSortOrder,
        toggleSelect: selection.toggleSelect,
        selectFolder,
        openFolder,
        selectFileItem,
        getSelectedFile,
        clearSelectedFile,
    };
}
