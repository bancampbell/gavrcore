import axios from 'axios';

import type {
    AccessLevel,
    AccessLevelPayload,
    Group,
    GroupPayload,
    PaginatedResponse,
    User,
    UserPayload,
} from '../../ui/types';

// ===== Users API =====
export const usersApi = {
    getAll: (params?: { search?: string; blocked?: boolean; activated?: boolean; page?: number }) =>
        axios.get<PaginatedResponse<User>>('/admin/users', { params }),

    getById: (id: number) =>
        axios.get<{ data: User }>(`/admin/users/${id}`),

    create: (data: UserPayload) =>
        axios.post<{ data: User }>('/admin/users', data),

    update: (id: number, data: UserPayload) =>
        axios.put<{ data: User }>(`/admin/users/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/users/${id}`),

    bulkBlock: (ids: number[]) =>
        axios.post('/admin/users/bulk-block', { ids }),

    bulkUnblock: (ids: number[]) =>
        axios.post('/admin/users/bulk-unblock', { ids }),
};

// ===== Groups API =====
export const groupsApi = {
    getAll: (params?: { search?: string; status?: boolean; page?: number }) =>
        axios.get<PaginatedResponse<Group>>('/admin/groups', { params }),

    getById: (id: number) =>
        axios.get<{ data: Group }>(`/admin/groups/${id}`),

    create: (data: GroupPayload) =>
        axios.post<{ data: Group }>('/admin/groups', data),

    update: (id: number, data: GroupPayload) =>
        axios.put<{ data: Group }>(`/admin/groups/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/groups/${id}`),

    updateStatus: (id: number, status: boolean) =>
        axios.post(`/admin/groups/${id}/status`, { status }),
};

// ===== Access Levels API =====
export const accessLevelsApi = {
    getAll: (params?: { search?: string; status?: boolean }) =>
        axios.get<AccessLevel[]>('/admin/access-levels', { params }),

    getById: (id: number) =>
        axios.get<{ data: AccessLevel }>(`/admin/access-levels/${id}`),

    create: (data: AccessLevelPayload) =>
        axios.post('/admin/access-levels', data),

    update: (id: number, data: AccessLevelPayload) =>
        axios.put(`/admin/access-levels/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/access-levels/${id}`),

    updateStatus: (id: number, status: boolean) =>
        axios.patch(`/admin/access-levels/${id}/status`, { status }),

    updateOrdering: (order: { id: number; ordering: number }[]) =>
        axios.post('/admin/access-levels/ordering', { order }),
};
