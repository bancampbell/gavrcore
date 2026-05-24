import axios from 'axios';
import type { MaterialsData, MaterialFilters } from '../types';

const getToken = () => localStorage.getItem('token');

const headers = () => ({
    headers: {
        Authorization: `Bearer ${getToken()}`
    }
});

export const materialsApi = {
    // Получить список материалов с фильтрацией и пагинацией
    getPaginated: async (filters?: MaterialFilters, page?: number): Promise<MaterialsData> => {
        const params = { ...filters, page };
        const response = await axios.get('/admin/materials', { params, ...headers() });
        return response.data;
    },

    // Переместить в корзину
    moveToTrash: async (ids: number[]): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/bulk-trash', { ids }, headers());
        return response.data;
    },

    // Восстановить из корзины
    restore: async (ids: number[]): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/restore', { ids }, headers());
        return response.data;
    },

    // Удалить навсегда
    forceDelete: async (ids: number[]): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/force-delete', { ids }, headers());
        return response.data;
    },

    // Очистить корзину
    emptyTrash: async (): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/empty-trash', {}, headers());
        return response.data;
    },

    // Опубликовать
    publish: async (ids: number[]): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/bulk-publish', { ids }, headers());
        return response.data;
    },

    // Снять с публикации
    unpublish: async (ids: number[]): Promise<{ message: string }> => {
        const response = await axios.post('/admin/materials/bulk-unpublish', { ids }, headers());
        return response.data;
    }
};
