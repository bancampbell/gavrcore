import axios from 'axios';
import type { Category, CategoryFormData, CategoryFilters, PaginatedData } from '../../ui/types';

const getToken = () => localStorage.getItem('token');

const headers = () => ({
    headers: {
        Authorization: `Bearer ${getToken()}`
    }
});

export const categoryApi = {
    getPaginated: async (filters?: CategoryFilters, page?: number): Promise<PaginatedData<Category>> => {
        const params = { ...filters, page };
        const response = await axios.get('/admin/categories', { params, ...headers() });
        return response.data;
    },

    getAllForSelect: async (): Promise<Record<number, string>> => {
        const response = await axios.get('/admin/categories/all', headers());
        return response.data;
    },

    getTree: async (): Promise<any[]> => {
        const response = await axios.get('/admin/categories/tree', headers());
        return response.data;
    },

    create: async (data: CategoryFormData): Promise<Category> => {
        const response = await axios.post('/admin/categories', data, headers());
        return response.data;
    },

    update: async (id: number, data: CategoryFormData): Promise<Category> => {
        const response = await axios.put(`/admin/categories/${id}`, data, headers());
        return response.data;
    },

    delete: async (id: number): Promise<void> => {
        await axios.delete(`/admin/categories/${id}`, headers());
    },

    bulkDelete: async (ids: number[]): Promise<void> => {
        await axios.post('/admin/categories/bulk-delete', { ids }, headers());
    }
};
