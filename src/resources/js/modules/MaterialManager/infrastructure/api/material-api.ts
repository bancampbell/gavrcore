import materialAxios from './material-axios';
import type { MaterialFilters, BulkResponse, CreateMaterialData, UpdateMaterialData, MaterialsData, Material } from '../types';

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

    getTrash: async (page?: number): Promise<MaterialsData> => {
        const response = await materialAxios.get('/trash', { params: { page } });
        return response.data;
    },

    getForEdit: async (id: number): Promise<{ material: Material; categories: unknown[]; user: unknown; title: string }> => {
        const response = await materialAxios.get(`/${id}/edit`);
        return response.data;
    },

    getList: async (): Promise<unknown[]> => {
        const response = await materialAxios.get('/list');
        return response.data;
    },

    create: async (data: CreateMaterialData): Promise<{ success: boolean; message: string; id: number }> => {
        const response = await materialAxios.post('/', data);
        return response.data;
    },

    update: async (id: number, data: UpdateMaterialData): Promise<{ success: boolean; message: string }> => {
        const response = await materialAxios.put(`/${id}`, data);
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
