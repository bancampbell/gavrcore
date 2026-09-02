export interface Category {
    id: number;
    name: string;
    alias: string;
    description: string | null;
    parent_id: number | null;
    depth: number;
    lft: number;
    rgt: number;
    is_active: boolean;
    published_count?: number;
    draft_count?: number;
    trash_count?: number;
    created_at?: string;
    updated_at?: string;
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
    is_active?: boolean;
}

export interface CategoryFilters {
    search?: string;
    parent_id?: number | null;
    is_active?: boolean | null;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export interface Notification {
    show: boolean;
    message: string;
    type: 'success' | 'error';
}
