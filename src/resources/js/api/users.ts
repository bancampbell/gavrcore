import axios from 'axios';

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    blocked: boolean;
    activated: boolean;
    last_login_at: string | null;
    last_login_ip: string | null;
    created_at: string;
    groups?: { id: number; name: string }[];
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

// Users API
export const usersApi = {
    getAll: (params?: { search?: string; blocked?: boolean; activated?: boolean; page?: number }) =>
        axios.get<PaginatedResponse<User>>('/admin/users', { params }),

    getById: (id: number) =>
        axios.get<{ data: User }>(`/admin/users/${id}`),

    create: (data: Partial<User>) =>
        axios.post<{ data: User }>('/admin/users', data),

    update: (id: number, data: Partial<User>) =>
        axios.put<{ data: User }>(`/admin/users/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/users/${id}`),

    bulkBlock: (ids: number[]) =>
        axios.post('/admin/users/bulk-block', { ids }),

    bulkUnblock: (ids: number[]) =>
        axios.post('/admin/users/bulk-unblock', { ids }),
};
