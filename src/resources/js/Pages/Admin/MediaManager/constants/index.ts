// resources/js/Pages/Admin/MediaManager/constants/index.ts

export const FILE_ICONS: Record<string, string> = {
    'pdf': '📄',
    'jpg': '🖼️',
    'jpeg': '🖼️',
    'png': '🖼️',
    'gif': '🖼️',
    'xlsx': '📊',
    'xls': '📊',
    'doc': '📃',
    'docx': '📃',
    'txt': '📝',
    'zip': '🗜️',
    'rar': '🗜️'
};

export const DEFAULT_FILE_ICON = '📄';
export const FOLDER_COLOR = '#D2B073';
export const HOVER_BG_COLOR = '#e6f0fa';
export const ACTIVE_BG_COLOR = '#e6f0fa';
export const ACTIVE_TEXT_COLOR = '#333';

export const SORT_BUTTONS = [
    { order: 'asc', title: 'Сортировка А-Я', icon: 'M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12' },
    { order: 'desc', title: 'Сортировка Я-А', icon: 'M3 4h13M3 8h9m-9 4h6m4 0l4 4m0 0l4-4m-4 4V4' }
] as const;

export const formatFileSize = (bytes: number): string => {
    if (!bytes || bytes === 0) return '';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

export const formatDate = (timestamp: number): string => {
    if (!timestamp) return '';
    const date = new Date(timestamp * 1000);
    return date.toLocaleDateString('ru-RU') + ', ' + date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

export const getFileIcon = (filename: string): string => {
    const ext = filename.split('.').pop()?.toLowerCase();
    return FILE_ICONS[ext || ''] || DEFAULT_FILE_ICON;
};
