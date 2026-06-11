// User types
export interface User {
    id: number;
    name: string;
    email: string;
}

// Category types
export interface Category {
    id: number;
    name: string;
    alias: string;
    description: string | null;
    parent_id: number | null;
    depth: number;
    published_count: number;
    draft_count: number;
    trash_count: number;
}

export interface CategoriesData {
    data: Category[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export interface CategoryFormData {
    name: string;
    alias: string;
    description: string;
    parent_id: number | null;
}

export interface CategoryFilters {
    search?: string;
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
    category?: Category;
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
