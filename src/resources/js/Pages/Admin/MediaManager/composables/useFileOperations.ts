// resources/js/Pages/Admin/MediaManager/composables/useFileOperations.ts

import { ref } from 'vue';
import axios from 'axios';

type NotificationFn = (message: string, type?: 'success' | 'error') => void;

export function useFileOperations(showNotification?: NotificationFn) {
    const loadingFolders = ref(false);
    const loadingContents = ref(false);
    const uploadLoading = ref(false);
    const uploadFiles = ref<FileList | null>(null);

    const setUploadFiles = (files: FileList | null) => {
        uploadFiles.value = files;
    };

    const loadFolders = async () => {
        loadingFolders.value = true;
        try {
            const response = await axios.get('/admin/media/folders', {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            return response.data;
        } catch (error) {
            console.error('Error loading folders:', error);
            showNotification?.('Ошибка загрузки папок', 'error');
            return [];
        } finally {
            loadingFolders.value = false;
        }
    };

    const loadContents = async (path: string) => {
        loadingContents.value = true;
        try {
            const response = await axios.get('/admin/media/contents', {
                params: { path },
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            return response.data;
        } catch (error) {
            console.error('Error loading contents:', error);
            showNotification?.('Ошибка загрузки содержимого', 'error');
            return [];
        } finally {
            loadingContents.value = false;
        }
    };

    const createFolder = async (name: string, path: string) => {
        try {
            await axios.post('/admin/media/folder', { name, path }, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification?.('Папка создана', 'success');
            return true;
        } catch (error: any) {
            console.error('Error creating folder:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка создания папки', 'error');
            return false;
        }
    };

    const renameItem = async (oldPath: string, newName: string) => {
        try {
            await axios.post('/admin/media/rename', { old_path: oldPath, new_name: newName }, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification?.('Переименовано успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error renaming item:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка переименования', 'error');
            return false;
        }
    };

    const deleteItem = async (path: string) => {
        try {
            await axios.delete('/admin/media/item', {
                data: { path },
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification?.('Удалено успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error deleting item:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка удаления', 'error');
            return false;
        }
    };

    // НОВАЯ ФУНКЦИЯ ДЛЯ МАССОВОГО УДАЛЕНИЯ
    const deleteItems = async (paths: string[]) => {
        if (!paths || paths.length === 0) {
            showNotification?.('Не выбрано ни одного элемента', 'error');
            return false;
        }

        try {
            await axios.delete('/admin/media/items', {
                data: { paths },
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification?.(`Удалено ${paths.length} элементов`, 'success');
            return true;
        } catch (error: any) {
            console.error('Error deleting items:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка удаления', 'error');
            return false;
        }
    };

    const copyItem = async (path: string) => {
        try {
            await axios.post('/admin/media/copy', { path }, {
                headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
            });
            showNotification?.('Скопировано успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error copying item:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка копирования', 'error');
            return false;
        }
    };

    const uploadFile = async (files: FileList, path: string) => {
        uploadLoading.value = true;
        const formData = new FormData();

        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        formData.append('path', path);

        try {
            await axios.post('/admin/media/upload', formData, {
                headers: {
                    Authorization: `Bearer ${localStorage.getItem('token')}`,
                    'Content-Type': 'multipart/form-data'
                }
            });
            showNotification?.('Файлы загружены успешно', 'success');
            return true;
        } catch (error: any) {
            console.error('Error uploading files:', error);
            showNotification?.(error.response?.data?.message || 'Ошибка загрузки', 'error');
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
        uploadFile
    };
}
