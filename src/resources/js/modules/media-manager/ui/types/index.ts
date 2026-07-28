export interface MediaItem {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

export interface Notification {
    show: boolean;
    message: string;
    type: 'success' | 'error';
}

export type SortOrder = 'asc' | 'desc';

export interface MediaManagerProps {
    user: User;
    mode?: 'full' | 'picker';
    onSelect?: (file: MediaItem) => void;
}

export interface User {
    id: number;
    name: string;
    email: string;
}
