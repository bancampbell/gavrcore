import { computed } from 'vue';
import { useMediaActions } from './useMediaActions';
import { useContents } from './useContents';
import { useSelection } from './useSelection';
import { useNavigation } from './useNavigation';
import { useModals } from './useModals';
import { usePicker } from './usePicker';
import type { MediaItem } from '../types';

export function useMediaManager(
    showNotification: (message: string, type: 'success' | 'error') => void,
    mode: 'full' | 'picker' = 'full',
    acceptedFiles?: string[],
) {
    const navigation = useNavigation();
    const actions = useMediaActions();
    const contents = useContents(actions, acceptedFiles, mode);
    const selection = useSelection();
    const modals = useModals();
    const picker = usePicker();

    const folderTree = computed((): Map<string, MediaItem[]> => {
        const map = new Map<string, MediaItem[]>();
        const sorted = [...contents.allFolders.value].sort((a, b) => a.name.localeCompare(b.name));

        for (const folder of sorted) {
            const pathStr = folder.path;
            const lastSlash = pathStr.lastIndexOf('/');
            const parentPath = lastSlash > 0 ? pathStr.substring(0, lastSlash) : '';

            if (!map.has(parentPath)) {
                map.set(parentPath, []);
            }
            const children = map.get(parentPath);
            if (children) {
                children.push(folder);
            }
        }

        return map;
    });

    const rootFolders = computed((): MediaItem[] => {
        return folderTree.value.get('') || [];
    });

    const currentPathDisplay = computed(() => navigation.currentPath.value || '/');
    const canGoBack = computed(() => navigation.currentPath.value !== '');
    const isAscActive = computed(() => contents.sortOrder.value === 'asc');
    const isDescActive = computed(() => contents.sortOrder.value === 'desc');

    const isSelected = (path: string) => {
        if (mode === 'picker') {
            return picker.isPicked(path) || selection.selectedItems.value.includes(path);
        }
        return selection.isSelected(path);
    };

    const loadData = async (page: number = 1) => {
        if (contents.allFolders.value.length === 0) {
            await contents.loadFolders();
        }
        await contents.load(navigation.currentPath.value, page);
        selection.clearSelection();
        if (mode === 'picker') {
            picker.unpickFile();
        }
    };

    const navigateToFolder = async (path: string) => {
        navigation.navigateToFolder(path);
        await loadData(1);
    };

    const goBack = async () => {
        navigation.goBack();
        await loadData(1);
    };

    const goHome = async () => {
        navigation.goHome();
        await loadData(1);
    };

    const handleCreateFolder = async (folderName: string) => {
        if (!folderName.trim()) return;
        const result = await actions.createFolder(folderName, navigation.currentPath.value);
        if (result.ok) {
            modals.closeCreateFolderModal();
            showNotification('Папка создана', 'success');
            await loadData();
        } else {
            showNotification(result.error || 'Ошибка создания папки', 'error');
        }
    };

    const handleRename = async (newName: string) => {
        if (!modals.renameItemData.value || !newName.trim()) return;
        if (newName === modals.renameItemData.value.name) {
            showNotification('Имя не изменено', 'error');
            modals.closeRenameModal();
            return;
        }

        const path = modals.renameItemData.value.path;
        const result = await actions.renameItem(path, newName);
        if (result.ok) {
            modals.closeRenameModal();
            showNotification('Переименовано успешно', 'success');
            await loadData();
        } else {
            showNotification(result.error || 'Ошибка переименования', 'error');
        }
    };

    const handleDelete = async () => {
        if (selection.selectedItems.value.length > 1) {
            const result = await actions.deleteItems(selection.selectedItems.value);
            if (result.ok) {
                modals.closeDeleteModal();
                showNotification(`Удалено ${selection.selectedItems.value.length} элементов`, 'success');
                await loadData();
            } else {
                showNotification(result.error || 'Ошибка удаления', 'error');
            }
            return;
        }

        if (!modals.deleteItemData.value) {
            showNotification('Не выбран элемент для удаления', 'error');
            return;
        }

        const path = modals.deleteItemData.value.path;
        const result = await actions.deleteItem(path);
        if (result.ok) {
            modals.closeDeleteModal();
            showNotification('Удалено успешно', 'success');
            await loadData();
        } else {
            showNotification(result.error || 'Ошибка удаления', 'error');
        }
    };

    const handleCopy = async () => {
        if (!selection.selectedItem.value) {
            showNotification('Не выбран элемент для копирования', 'error');
            return;
        }

        const path = selection.selectedItem.value.path;
        const result = await actions.copyItem(path);
        if (result.ok) {
            showNotification('Скопировано успешно', 'success');
            await loadData();
        } else {
            showNotification(result.error || 'Ошибка копирования', 'error');
        }
    };

    const handleUpload = async (files: FileList) => {
        if (!files || files.length === 0) return false;
        const result = await actions.uploadFile(files, navigation.currentPath.value);
        if (result.ok) {
            modals.closeUploadModal();
            showNotification('Файлы загружены успешно', 'success');
            await loadData();
            return true;
        } else {
            showNotification(result.error || 'Ошибка загрузки', 'error');
            return false;
        }
    };

    const selectFolder = (item: MediaItem) => {
        if (!item) return;
        selection.selectItem(item);
    };

    const openFolder = (item: MediaItem) => {
        if (!item) return;
        navigateToFolder(item.path);
    };

    const selectFileItem = (item: MediaItem) => {
        if (!item) return;
        if (mode === 'picker') {
            const isCurrentlySelected = picker.isPicked(item.path);
            const fileData = picker.togglePick(item, isCurrentlySelected);
            if (fileData) {
                selection.selectedItems.value = [item.path];
                selection.selectedItem.value = item;
            } else {
                selection.clearSelection();
            }
        } else {
            selection.selectItem(item);
        }
    };

    const toggleSelect = (path: string, item: MediaItem) => {
        if (mode === 'picker' && item.type === 'file') {
            const isCurrentlySelected = picker.isPicked(path) || selection.selectedItems.value.includes(path);
            const fileData = picker.togglePick(item, isCurrentlySelected);
            if (fileData) {
                selection.selectedItems.value = [path];
                selection.selectedItem.value = item;
            } else {
                selection.clearSelection();
            }
        } else {
            selection.toggleSelect(path, item);
        }
    };

    const openDeleteModal = () => {
        if (selection.selectedItems.value.length === 0 && !selection.selectedItem.value) {
            showNotification('Ничего не выбрано для удаления', 'error');
            return;
        }

        if (selection.selectedItems.value.length > 1) {
            modals.openDeleteModal(null);
        } else if (selection.selectedItem.value) {
            modals.openDeleteModal(selection.selectedItem.value);
        }
    };

    const openRenameModal = () => {
        if (!selection.selectedItem.value) {
            showNotification('Ничего не выбрано для переименования', 'error');
            return;
        }
        modals.openRenameModal(selection.selectedItem.value);
    };

    return {
        allFolders: contents.allFolders,
        contents: contents.contents,
        currentPath: navigation.currentPath,
        selectedFileForPicker: picker.selectedFileForPicker,
        showCreateModal: modals.showCreateModal,
        showRenameModal: modals.showRenameModal,
        showDeleteModal: modals.showDeleteModal,
        showUploadModal: modals.showUploadModal,
        renameItemData: modals.renameItemData,
        deleteItemData: modals.deleteItemData,
        showSearch: contents.showSearch,
        searchQuery: contents.searchQuery,
        sortOrder: contents.sortOrder,
        selectedItems: selection.selectedItems,
        selectedItem: selection.selectedItem,
        uploadLoading: actions.loading,
        foldersLoading: contents.foldersLoading,
        loadingContents: contents.loading,
        rootFolders,
        folderTree,
        sortedFilteredFolders: contents.sortedFilteredFolders,
        sortedFilteredFiles: contents.sortedFilteredFiles,
        currentPathDisplay,
        foldersCount: contents.foldersCount,
        filesCount: contents.filesCount,
        totalItemsCount: contents.totalCount,
        canGoBack,
        isAscActive,
        isDescActive,
        paginatedData: contents.paginatedData,
        loadData,
        loadFolders: contents.loadFolders,
        navigateToFolder,
        goBack,
        goHome,
        openCreateFolderModal: modals.openCreateFolderModal,
        handleCreateFolder,
        openRenameModal,
        handleRename,
        openDeleteModal,
        handleDelete,
        handleCopy,
        openUploadModal: modals.openUploadModal,
        handleUpload,
        clearSearch: contents.clearSearch,
        openSearch: contents.openSearch,
        setSortOrder: contents.setSortOrder,
        toggleSelect,
        selectFolder,
        openFolder,
        selectFileItem,
        isSelected,
    };
}
