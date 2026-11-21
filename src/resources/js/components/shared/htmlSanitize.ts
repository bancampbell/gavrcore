import DOMPurify from 'dompurify';
import type { Config } from 'dompurify';

// Хосты, с которых разрешён <iframe> (встраивание карт/видео). Эталон — Яндекс.Карты
// (map-widget с yandex.ru). Любой другой src — http:, data:, javascript:, чужой хост —
// приводит к вырезанию тега целиком (XSS-гигиена: см. TECHDEBT п.58).
export const IFRAME_ALLOWED_HOSTS: readonly string[] = [
    'yandex.ru',
    'yastatic.net',
    'api-maps.yandex.ru',
    'www.google.com',
    'maps.google.com',
    'www.openstreetmap.org',
];

let iframeHookRegistered = false;

function ensureIframeSrcHook(): void {
    if (iframeHookRegistered) return;

    DOMPurify.addHook('afterSanitizeAttributes', (node) => {
        if (node.nodeName !== 'IFRAME') return;

        let allowed = false;
        const src = node.getAttribute('src');
        if (src) {
            try {
                const url = new URL(src);
                allowed = url.protocol === 'https:' && IFRAME_ALLOWED_HOSTS.includes(url.hostname);
            } catch {
                allowed = false;
            }
        }

        if (!allowed) {
            node.remove();
        }
    });

    iframeHookRegistered = true;
}

// DOMPurify.sanitize + разрешённый <iframe> с src строго из белого списка хостов (только https).
// Базовый конфиг (ALLOWED_TAGS/ALLOWED_ATTR и т.д.) задаёт вызывающая сторона.
export function sanitizeHtml(html: string, config: Config): string {
    ensureIframeSrcHook();

    return DOMPurify.sanitize(html, {
        ...config,
        ADD_TAGS: [...(config.ADD_TAGS ?? []), 'iframe'],
        ADD_ATTR: [...(config.ADD_ATTR ?? []), 'frameborder', 'allowfullscreen', 'loading', 'referrerpolicy'],
    });
}
