import axios from 'axios';

import type {
    AccessLevelPayload,
    Group,
    GroupPayload,
    User,
    UserPayload,
} from '../../ui/types';

// ===== Users API =====
export const usersApi = {
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
    create: (data: AccessLevelPayload) =>
        axios.post('/admin/access-levels', data),

    update: (id: number, data: AccessLevelPayload) =>
        axios.put(`/admin/access-levels/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/access-levels/${id}`),

    updateStatus: (id: number, status: boolean) =>
        axios.patch(`/admin/access-levels/${id}/status`, { status }),
};
