import materialAxios from './material-axios';
import type { MaterialFilters, BulkResponse, MaterialsData } from '../types';

let searchAbortController: AbortController | null = null;

export const materialApi = {
    getPaginated: async (filters?: MaterialFilters, page?: number): Promise<MaterialsData> => {
        if (searchAbortController) {
            searchAbortController.abort();
        }
        searchAbortController = new AbortController();

        const params = { ...filters, page };
        const response = await materialAxios.get('/', {
            params,
            signal: searchAbortController.signal,
        });
        return response.data;
    },

    moveToTrash: async (ids: number[]): Promise<BulkResponse> => {
        const response = await materialAxios.post('/bulk-trash', { ids });
        return response.data;
    },

    restore: async (ids: number[]): Promise<BulkResponse> => {
        const response = await materialAxios.post('/restore', { ids });
        return response.data;
    },

    forceDelete: async (ids: number[]): Promise<BulkResponse> => {
        const response = await materialAxios.post('/force-delete', { ids });
        return response.data;
    },

    emptyTrash: async (): Promise<BulkResponse> => {
        const response = await materialAxios.post('/empty-trash', {});
        return response.data;
    },

    publish: async (ids: number[]): Promise<BulkResponse> => {
        const response = await materialAxios.post('/bulk-publish', { ids });
        return response.data;
    },

    unpublish: async (ids: number[]): Promise<BulkResponse> => {
        const response = await materialAxios.post('/bulk-unpublish', { ids });
        return response.data;
    },

    toggleHomepage: async (id: number, showOnHomepage: boolean): Promise<{ message: string }> => {
        const response = await materialAxios.put(`/${id}`, { show_on_homepage: showOnHomepage });
        return response.data;
    }
};
