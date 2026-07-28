import { ref } from 'vue';
import { getMediaManagerContainer } from '../../infrastructure/di/container';
import type { MediaItem } from '../../domain/entities/MediaItem';

export type NotificationFn = (message: string, type: 'success' | 'error') => void;

export function useFileOperations(showNotification: NotificationFn) {
    const { mediaRepository } = getMediaManagerContainer();

    const loadingFolders = ref(false);
    const loadingContents = ref(false);
    const uploadLoading = ref(false);
    const uploadFiles = ref<FileList | null>(null);

    const setUploadFiles = (files: FileList | null) => {
        uploadFiles.value = files;
    };

    const loadFolders = async (): Promise<MediaItem[]> => {
        loadingFolders.value = true;
        try {
            return await mediaRepository.loadFolders();
        } catch (error) {
            console.error('Error loading folders:', error);
            showNotification('Ошибка загрузки папок', 'error');
            return [];
        } finally {
            loadingFolders.value = false;
        }
    };

    const loadContents = async (path: string): Promise<MediaItem[]> => {
        loadingContents.value = true;
        try {
            return await mediaRepository.loadContents(path);
        } catch (error) {
            console.error('Error loading contents:', error);
            showNotification('Ошибка загрузки содержимого', 'error');
            return [];
        } finally {
            loadingContents.value = false;
        }
    };

    const createFolder = async (name: string, path: string): Promise<boolean> => {
        try {
            await mediaRepository.createFolder(name, path);
            showNotification('Папка создана', 'success');
            return true;
        } catch (error: any) {
            console.error('Error creating folder:', error);
            showNotification(error.response?.data?.message || 'Ошибка создания папки', 'error');
            return false;
        }
    };

    const renameItem = async (oldPath: string, newName: string): Promise<boolean> => {
        try {
            await mediaRepository.renameItem(oldPath, newName);
            showNotification('Переименовано успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error renaming item:', error);
            showNotification(error.response?.data?.message || 'Ошибка переименования', 'error');
            return false;
        }
    };

    const deleteItem = async (path: string): Promise<boolean> => {
        try {
            await mediaRepository.deleteItem(path);
            showNotification('Удалено успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error deleting item:', error);
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
            return false;
        }
    };

    const deleteItems = async (paths: string[]): Promise<boolean> => {
        if (!paths || paths.length === 0) {
            showNotification('Не выбрано ни одного элемента', 'error');
            return false;
        }

        try {
            await mediaRepository.deleteItems(paths);
            showNotification(`Удалено ${paths.length} элементов`, 'success');
            return true;
        } catch (error: any) {
            console.error('Error deleting items:', error);
            showNotification(error.response?.data?.message || 'Ошибка удаления', 'error');
            return false;
        }
    };

    const copyItem = async (path: string): Promise<boolean> => {
        try {
            await mediaRepository.copyItem(path);
            showNotification('Скопировано успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error copying item:', error);
            showNotification(error.response?.data?.message || 'Ошибка копирования', 'error');
            return false;
        }
    };

    const uploadFile = async (files: FileList, path: string): Promise<boolean> => {
        uploadLoading.value = true;
        try {
            await mediaRepository.uploadFile(files, path);
            showNotification('Файлы загружены успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error uploading files:', error);
            showNotification(error.response?.data?.message || 'Ошибка загрузки', 'error');
            return false;
        } finally {
            uploadLoading.value = false;
        }
    };

    return {
        loadingFolders,
        loadingContents,
        uploadLoading,
        uploadFiles,
        setUploadFiles,
        loadFolders,
        loadContents,
        createFolder,
        renameItem,
        deleteItem,
        deleteItems,
        copyItem,
        uploadFile,
    };
}
