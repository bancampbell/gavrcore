import axios from 'axios';
import type { Category, CategoryFormData } from '../../ui/types';

export const categoryApi = {
    create: async (data: CategoryFormData): Promise<Category> => {
        const response = await axios.post('/admin/categories', data);
        return response.data;
    },

    update: async (id: number, data: CategoryFormData): Promise<Category> => {
        const response = await axios.put(`/admin/categories/${id}`, data);
        return response.data;
    },

    delete: async (id: number): Promise<void> => {
        await axios.delete(`/admin/categories/${id}`);
    },

    bulkDelete: async (ids: number[]): Promise<void> => {
        await axios.post('/admin/categories/bulk-delete', { ids });
    }
};
