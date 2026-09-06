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

export interface Permission {
    id: number;
    name: string;
    key: string;
    group: string | null;
    description: string | null;
}

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

export interface AccessLevel {
    id: number;
    title: string;
    alias: string;
    description: string | null;
    status: boolean;
    ordering: number;
    groups: Group[];
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

export interface UserPayload {
    name: string;
    username: string;
    email: string;
    password?: string;
    blocked: boolean;
    activated: boolean;
    groups?: number[];
}

export interface GroupPayload {
    name: string;
    alias?: string;
    description?: string | null;
    status: boolean;
    ordering?: number;
    permissions?: number[];
}

export interface AccessLevelPayload {
    title: string;
    alias?: string;
    description?: string | null;
    status: boolean;
    groups?: number[];
}

export interface Notification {
    show: boolean;
    message: string;
    type: string;
}
