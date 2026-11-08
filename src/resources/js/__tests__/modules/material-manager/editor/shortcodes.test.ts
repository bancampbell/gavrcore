import { describe, it, expect } from 'vitest';
import { buildGalleryShortcode, buildFormShortcode } from '@/modules/MaterialManager/ui/editor/shortcodes';

// Regex-парсеры из components/shared/ShortcodeRenderer.vue — сверять при изменении.
const GALLERY_SHORTCODE_RE = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/;
const FORM_SHORTCODE_RE = /\[form\s+id="(\d+)"(?:\s+([^\]]*))?\]/;

describe('shortcodes: сборка шорткодов (TECHDEBT п.27)', () => {
    it('название галереи с двойной кавычкой экранируется &quot; и парсится regex целиком', () => {
        const shortcode = buildGalleryShortcode(7, 'Галерея "Офис"');
        expect(shortcode).toBe('[gallery id="7" name="Галерея &quot;Офис&quot;"]');

        // Контраст: неэкранированная кавычка рвёт парсер — regex не матчит шорткод вообще.
        const broken = '[gallery id="7" name="Галерея "Офис""]'.match(GALLERY_SHORTCODE_RE);
        expect(broken).toBeNull();

        const match = shortcode.match(GALLERY_SHORTCODE_RE);
        expect(match).not.toBeNull();
        expect(match![0]).toBe(shortcode);
        expect(match![1]).toBe('7');
        expect(match![2]).toBe('Галерея &quot;Офис&quot;');
    });

    it('название без кавычек не изменяется', () => {
        const shortcode = buildGalleryShortcode(3, 'Фото с выставки');
        expect(shortcode).toBe('[gallery id="3" name="Фото с выставки"]');
        expect(GALLERY_SHORTCODE_RE.test(shortcode)).toBe(true);
    });

    it('одинарные кавычки в названии не требуют экранирования и пропускаются regex', () => {
        const shortcode = buildGalleryShortcode(4, "Кафе 'Лира'");
        const match = shortcode.match(GALLERY_SHORTCODE_RE);
        expect(match).not.toBeNull();
        expect(match![0]).toBe(shortcode);
        expect(match![2]).toBe("Кафе 'Лира'");
    });

    it('form-шорткод валиден и парсится', () => {
        const shortcode = buildFormShortcode(5);
        expect(shortcode).toBe('[form id="5"]');
        expect(FORM_SHORTCODE_RE.test(shortcode)).toBe(true);
    });
});
