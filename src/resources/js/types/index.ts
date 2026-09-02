// User types
export interface User {
    id: number;
    name: string;
    email: string;
}

// Material types
export interface Material {
    id: number;
    title: string;
    alias: string;
    content: string | null;
    category_id: number;
    user_id: number;
    state: 'published' | 'draft' | 'archived';
    access: 'public' | 'registered' | 'special';
    views: number;
    created_at: string;
    updated_at: string;
    category?: any;
    user?: User;
    show_on_homepage?: boolean;
    featured?: string;
}

export interface MaterialsData {
    data: Material[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

// Common types
export interface Notification {
    show: boolean;
    message: string;
    type: 'success' | 'error';
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export interface MaterialFilters {
    search?: string;
    state?: string;
    category_id?: number | null;
    access?: string;
    author?: number | null;
}
