export interface Material {
    id: number;
    title: string;
    slug: string;
    alias: string;
    content: string | null;
    category_id: number | null;
    user_id: number;
    state: 'published' | 'draft' | 'archived' | 'trash';
    access: 'public' | 'registered' | 'special';
    views: number;
    published_at: string | null;
    featured: boolean;
    show_on_homepage: boolean;
    show_date: boolean;
    show_author: boolean;
    show_category: boolean;
    show_views: boolean;
    use_global_settings: boolean;
    template: string | null;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    category?: {
        id: number;
        name: string;
        slug: string;
    };
    user?: {
        id: number;
        name: string;
        email: string;
    };
    status_label: string;
    access_label: string;
    status_color: string;
}

export interface MaterialFilters {
    search?: string;
    state?: string;
    category_id?: number | null;
    access?: string;
    author?: number | null;
    per_page?: number;
    sort?: string;
    direction?: string;
}

export interface BulkResponse {
    message: string;
}

export interface MaterialsData {
    data: Material[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface CreateMaterialData {
    title: string;
    slug?: string | null;
    content?: string | null;
    category_id?: number | null;
    state?: string;
    access?: string;
    show_on_homepage?: boolean;
    show_date?: boolean;
    show_author?: boolean;
    show_category?: boolean;
    show_views?: boolean;
    use_global_settings?: boolean;
    meta_title?: string | null;
    meta_description?: string | null;
    meta_keywords?: string | null;
}

export interface UpdateMaterialData {
    title?: string;
    slug?: string | null;
    content?: string | null;
    category_id?: number | null;
    state?: string;
    access?: string;
    show_on_homepage?: boolean;
    show_date?: boolean;
    show_author?: boolean;
    show_category?: boolean;
    show_views?: boolean;
    use_global_settings?: boolean;
    template?: string | null;
    meta_title?: string | null;
    meta_description?: string | null;
    meta_keywords?: string | null;
}

export interface Notification {
    show: boolean;
    message: string;
    type: 'success' | 'error';
}
