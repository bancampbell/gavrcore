import { ref } from 'vue';
import { mediaApi } from '../../infrastructure/api/media-api';
import type { MediaItem } from '../types';

export function useMediaActions() {
    const loading = ref(false);

    const wrap = async <T>(fn: () => Promise<T>): Promise<{ ok: boolean; error?: string }> => {
        loading.value = true;
        try {
            await fn();
            return { ok: true };
        } catch (e: any) {
            return { ok: false, error: e.message || 'Неизвестная ошибка' };
        } finally {
            loading.value = false;
        }
    };

    const loadContents = async (path: string): Promise<MediaItem[]> => {
        loading.value = true;
        try {
            return await mediaApi.loadContents(path);
        } finally {
            loading.value = false;
        }
    };

    const loadPaginatedContents = async (
        path: string,
        page: number = 1,
        perPage: number = 20,
        sort: string = 'name_asc',
        search: string | null = null,
    ) => {
        loading.value = true;
        try {
            return await mediaApi.loadPaginatedContents(path, page, perPage, sort, search);
        } finally {
            loading.value = false;
        }
    };

    const loadFolders = async (): Promise<MediaItem[]> => {
        loading.value = true;
        try {
            return await mediaApi.loadFolders();
        } finally {
            loading.value = false;
        }
    };

    const createFolder = async (name: string, path: string): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.createFolder(name, path));
    };

    const renameItem = async (oldPath: string, newName: string): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.renameItem(oldPath, newName));
    };

    const deleteItem = async (path: string): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.deleteItem(path));
    };

    const deleteItems = async (paths: string[]): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.deleteItems(paths));
    };

    const copyItem = async (path: string): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.copyItem(path));
    };

    const uploadFile = async (files: FileList, path: string): Promise<{ ok: boolean; error?: string }> => {
        return wrap(() => mediaApi.uploadFile(files, path));
    };

    return {
        loading,
        loadContents,
        loadPaginatedContents,
        loadFolders,
        createFolder,
        renameItem,
        deleteItem,
        deleteItems,
        copyItem,
        uploadFile,
    };
}
