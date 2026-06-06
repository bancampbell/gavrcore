import axios from 'axios';

export interface Group {
    id: number;
    name: string;
    alias: string;
    description: string | null;
    status: boolean;
    ordering: number;
    created_at?: string;
    updated_at?: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export const groupsApi = {
    getAll: (params?: { search?: string; status?: boolean; page?: number }) =>
        axios.get<PaginatedResponse<Group>>('/admin/groups', { params }),

    getById: (id: number) =>
        axios.get<{ data: Group }>(`/admin/groups/${id}`),

    create: (data: Partial<Group>) =>
        axios.post<{ data: Group }>('/admin/groups', data),

    update: (id: number, data: Partial<Group>) =>
        axios.put<{ data: Group }>(`/admin/groups/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/groups/${id}`),

    updateStatus: (id: number, status: boolean) =>
        axios.post(`/admin/groups/${id}/status`, { status }),
};
