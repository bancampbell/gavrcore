export interface MenuType {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    ordering: number;
    status: boolean;
    items_count?: number;
    created_at?: string;
    updated_at?: string;
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
    created_at?: string;
    updated_at?: string;
}

export interface MenuItemFormData {
    parent_id: number | null;
    title: string;
    alias: string;
    link_type: string;
    link_value: string | null;
    target: string;
    status: boolean;
    access: string;
    language: string;
}
