import axios from 'axios';
import type { Category, CategoryFormData, CategoryFilters, PaginatedData } from '../types';

const getToken = () => localStorage.getItem('token');

const headers = () => ({
    headers: {
        Authorization: `Bearer ${getToken()}`
    }
});

export const categoriesApi = {
    // Получить список категорий с фильтрацией и пагинацией
    getPaginated: async (filters?: CategoryFilters, page?: number): Promise<PaginatedData<Category>> => {
        const params = { ...filters, page };
        const response = await axios.get('/admin/categories', { params, ...headers() });
        return response.data;
    },

    // Получить все категории для select
    getAllForSelect: async (): Promise<Record<number, string>> => {
        const response = await axios.get('/admin/categories/all', headers());
        return response.data;
    },

    // Создать категорию
    create: async (data: CategoryFormData): Promise<Category> => {
        const response = await axios.post('/admin/categories', data, headers());
        return response.data;
    },

    // Обновить категорию
    update: async (id: number, data: CategoryFormData): Promise<Category> => {
        const response = await axios.post(`/admin/categories/${id}`, {
            _method: 'PUT',
            ...data
        }, headers());
        return response.data;
    },

    // Удалить категорию
    delete: async (id: number): Promise<void> => {
        await axios.delete(`/admin/categories/${id}`, headers());
    },

    // Массовое удаление
    bulkDelete: async (ids: number[]): Promise<void> => {
        const promises = ids.map(id => axios.delete(`/admin/categories/${id}`, headers()));
        await Promise.all(promises);
    }
};
