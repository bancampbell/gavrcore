/**
 * Константы модуля UserManager
 */

/** Базовые URL административного раздела */
export const ADMIN_URLS = {
    users: '/admin/users',
    groups: '/admin/groups',
    accessLevels: '/admin/access-levels',
} as const;

/** Дебаунс поиска, мс */
export const SEARCH_DEBOUNCE_MS = 500;

/** Автоскрытие уведомлений, мс */
export const NOTIFICATION_TIMEOUT_MS = 3000;

/** Размер страницы по умолчанию */
export const DEFAULT_PER_PAGE = 20;
