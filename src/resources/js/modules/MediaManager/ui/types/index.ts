export interface MediaItem {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

export interface PaginatedResponse {
    data: MediaItem[];
    total: number;
    page: number;
    per_page: number;
    last_page: number;
}

export interface OperationResultData {
    message: string;
    data?: Record<string, unknown>;
}

export type Result<T = void> =
    | { ok: true; data: T }
    | { ok: false; error: string };

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

export const ALLOWED_EXTENSIONS = [
    'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico',
    'mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv',
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf',
    'zip', 'rar', '7z', 'tar', 'gz',
    'js', 'ts', 'vue', 'html', 'css', 'scss', 'php', 'py', 'rb', 'go', 'rs',
    'json', 'xml', 'yaml', 'yml',
];

export const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
export const VIDEO_EXTENSIONS = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'];
export const MAX_FILE_SIZE = 100 * 1024 * 1024;

export const FILE_ICONS: Record<string, string> = {
    'pdf': '📄',
    'jpg': '🖼️',
    'jpeg': '🖼️',
    'png': '🖼️',
    'gif': '🖼️',
    'webp': '🖼️',
    'svg': '🖼️',
    'bmp': '🖼️',
    'ico': '🖼️',
    'xlsx': '📊',
    'xls': '📊',
    'doc': '📃',
    'docx': '📃',
    'txt': '📝',
    'zip': '🗜️',
    'rar': '🗜️',
    '7z': '🗜️',
    'tar': '🗜️',
    'gz': '🗜️',
    'mp4': '🎬',
    'webm': '🎬',
    'ogg': '🎬',
    'mov': '🎬',
    'avi': '🎬',
    'mkv': '🎬',
};

export const DEFAULT_FILE_ICON = '📄';

export const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

export const formatDate = (timestamp: number): string => {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleDateString('ru-RU') + ', ' + date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

export const getFileIcon = (filename: string): string => {
    const ext = filename.split('.').pop()?.toLowerCase() || '';
    return FILE_ICONS[ext] || DEFAULT_FILE_ICON;
};
