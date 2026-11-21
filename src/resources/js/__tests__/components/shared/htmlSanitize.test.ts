import { describe, it, expect } from 'vitest';
import { sanitizeHtml, IFRAME_ALLOWED_HOSTS } from '@/components/shared/htmlSanitize';

// Конфиги зеркалят точки вызова: save-path (editor/Index.vue) и render-path (ShortcodeRenderer.vue).
const EDITOR_CONFIG = {
    ALLOWED_TAGS: [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'img', 'div', 'span',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'section', 'article', 'header', 'footer', 'nav',
    ],
    ALLOWED_ATTR: ['href', 'target', 'title', 'src', 'alt', 'width', 'height', 'style', 'class', 'id', 'data-align', 'data-float', 'rowspan', 'colspan'],
    ALLOW_DATA_ATTR: false,
    ADD_URI_SAFE_ATTR: ['src', 'href'],
};

const RENDERER_CONFIG = {
    ...EDITOR_CONFIG,
    ALLOWED_ATTR: ['href', 'target', 'title', 'src', 'alt', 'width', 'height', 'style', 'class', 'id', 'data-form-id', 'data-gallery-id'],
    ALLOW_DATA_ATTR: true,
};

const YA_MAPS_IFRAME = '<iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3Aabc123&source=constructor" width="570" height="380" frameborder="0"></iframe>';

// innerHTML/DOMPurify сериализуют & в атрибутах как &amp; — матчим оба варианта.
const YA_SRC_RE = /src="https:\/\/yandex\.ru\/map-widget\/v1\/\?um=constructor%3Aabc123(?:&amp;|&)source=constructor"/;

describe('sanitizeHtml: iframe Яндекс.Карт (save-path и render-path)', () => {
    it('эталонный iframe Яндекс.Карт проходит целиком: тег, src, width/height, frameborder', () => {
        const out = sanitizeHtml(YA_MAPS_IFRAME, EDITOR_CONFIG);

        expect(out).toContain('<iframe');
        expect(out).toMatch(YA_SRC_RE);
        expect(out).toContain('width="570"');
        expect(out).toContain('height="380"');
        expect(out).toContain('frameborder="0"');
        expect(out).toContain('</iframe>');
    });

    it('эталонный iframe проходит и в render-конфиге (ShortcodeRenderer)', () => {
        const out = sanitizeHtml(YA_MAPS_IFRAME, RENDERER_CONFIG);

        expect(out).toContain('<iframe');
        expect(out).toMatch(YA_SRC_RE);
    });

    it.each([
        ['javascript:', '<iframe src="javascript:alert(1)"></iframe>'],
        ['data:', '<iframe src="data:text/html,<script>alert(1)</script>"></iframe>'],
        ['чужой https-хост', '<iframe src="https://evil.example.com/x"></iframe>'],
        ['http вместо https', '<iframe src="http://yandex.ru/map-widget/v1/"></iframe>'],
        ['поддомен вне белого списка', '<iframe src="https://maps.yandex.ru/frame"></iframe>'],
        ['без src', '<iframe width="570" height="380"></iframe>'],
    ])('режет вредоносный iframe: %s', (_case, html) => {
        expect(sanitizeHtml(html, EDITOR_CONFIG)).not.toContain('<iframe');
        expect(sanitizeHtml(html, RENDERER_CONFIG)).not.toContain('<iframe');
    });

    it('разрешённые дополнительные атрибуты iframe сохраняются', () => {
        const out = sanitizeHtml(
            '<iframe src="https://www.google.com/maps/embed" allowfullscreen loading="lazy" referrerpolicy="no-referrer"></iframe>',
            EDITOR_CONFIG,
        );

        expect(out).toContain('<iframe');
        expect(out).toContain('allowfullscreen');
        expect(out).toContain('loading="lazy"');
        expect(out).toContain('referrerpolicy="no-referrer"');
    });

    it('обработчики событий на iframe вырезаются, сам iframe остаётся', () => {
        const out = sanitizeHtml(
            '<iframe src="https://yandex.ru/map-widget/v1/" onload="alert(1)" onclick="alert(2)"></iframe>',
            EDITOR_CONFIG,
        );

        expect(out).toContain('<iframe');
        expect(out).not.toContain('onload');
        expect(out).not.toContain('onclick');
    });

    it('обычная разметка не ломается: ссылки, картинки, стили', () => {
        const html = '<p class="intro" style="text-align: center;">Текст <a href="https://example.com/page" target="_blank">ссылка</a></p><img src="/storage/img.png" alt="Фото" width="300">';
        const out = sanitizeHtml(html, EDITOR_CONFIG);

        expect(out).toContain('<p class="intro"');
        expect(out).toContain('href="https://example.com/page"');
        expect(out).toContain('<img src="/storage/img.png"');
        expect(out).toContain('alt="Фото"');
    });

    it('шорткод-плейсхолдеры render-path не ломаются: data-form-id / data-gallery-id', () => {
        const html = '<div data-form-id="5" class="form-placeholder"></div><div data-gallery-id="7" class="gallery-placeholder"></div>';
        const out = sanitizeHtml(html, RENDERER_CONFIG);

        expect(out).toContain('data-form-id="5"');
        expect(out).toContain('data-gallery-id="7"');
        expect(out).not.toContain('<iframe');
    });

    it('все хосты белого списка проходят по https', () => {
        for (const host of IFRAME_ALLOWED_HOSTS) {
            const out = sanitizeHtml(`<iframe src="https://${host}/embed"></iframe>`, EDITOR_CONFIG);
            expect(out).toContain('<iframe');
            expect(out).toContain(`src="https://${host}/embed"`);
        }
    });
});
