// resources/js/api/menu.ts
import axios from 'axios';

export interface MenuType {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    ordering: number;
    status: boolean;
    items_count?: number;
}

export interface MenuItem {
    id: number;
    menu_type_id: number;
    parent_id: number | null;
    title: string;
    alias: string;
    link_type: 'url' | 'material' | 'separator' | 'heading' | 'external';
    link_value: string | null;
    target: '_self' | '_blank';
    ordering: number;
    status: boolean;
    access: string;
    language: string;
    children?: MenuItem[];
    menu_type?: { title: string };
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

// Menu Types API
export const menuTypesApi = {
    getAll: (params?: { search?: string; status?: boolean; per_page?: number }) =>
        axios.get<{ data: MenuType[] }>('/admin/menu/types', { params }),

    getById: (id: number) =>
        axios.get<{ data: MenuType }>(`/admin/menu/types/${id}`),

    create: (data: Partial<MenuType>) =>
        axios.post<{ data: MenuType }>('/admin/menu/types', data),

    update: (id: number, data: Partial<MenuType>) =>
        axios.put<{ data: MenuType }>(`/admin/menu/types/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/menu/types/${id}`),

    updateOrdering: (order: { id: number; ordering: number }[]) =>
        axios.post('/admin/menu/types/ordering/update', { order }),

    updateStatus: (id: number, status: boolean) =>
        axios.post(`/admin/menu/types/${id}/status`, { status }),
};

// Menu Items API
export const menuItemsApi = {
    getAll: (menuTypeId: number, params?: { search?: string; status?: boolean; per_page?: number }) =>
        axios.get<{ data: MenuItem[] }>(`/admin/menu/types/${menuTypeId}/items`, { params }),

    getAllItems: (params?: { search?: string; status?: boolean; page?: number }) =>
        axios.get<PaginatedResponse<MenuItem>>('/admin/menu/items/all', { params }),

    getTree: (menuTypeId: number) =>
        axios.get<MenuItem[]>(`/admin/menu/types/${menuTypeId}/items/tree`),

    getById: (id: number) =>
        axios.get<{ data: MenuItem }>(`/admin/menu/items/${id}`),

    create: (menuTypeId: number, data: Partial<MenuItem>) =>
        axios.post<{ data: MenuItem }>(`/admin/menu/types/${menuTypeId}/items`, data),

    update: (id: number, data: Partial<MenuItem>) =>
        axios.put<{ data: MenuItem }>(`/admin/menu/items/${id}`, data),

    delete: (id: number) =>
        axios.delete(`/admin/menu/items/${id}`),

    updateStatus: (id: number, status: boolean) =>
        axios.post(`/admin/menu/items/${id}/status`, { status }),

    updateOrdering: (order: { id: number; ordering: number }[]) =>
        axios.post('/admin/menu/items/ordering/update', { order }),
};
