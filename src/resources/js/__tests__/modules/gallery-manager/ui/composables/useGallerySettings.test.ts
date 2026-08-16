import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import type { VueWrapper } from '@vue/test-utils';
import { useGallerySettings } from '@/modules/GalleryManager/ui/composables/useGallerySettings';

function withSetup<T>(composable: () => T): [T, VueWrapper] {
    let result!: T;
    const wrapper = mount({
        setup() {
            result = composable();
            return {};
        },
        template: '<div></div>',
    });
    return [result, wrapper];
}

describe('useGallerySettings', () => {
    it('returns deep copy of default settings when no saved settings provided', () => {
        const [{ settings }] = withSetup(() => useGallerySettings());

        expect(settings.gutter).toBe(20);
        expect(settings.match_height).toBe(true);
        expect(settings.alignment).toBe('left');
        expect(settings.media).toEqual({ width: 'auto', height: 'auto', border: 'none' });
        expect(settings.content).toEqual({ show_title: true, show_content: false, title_size: 'default' });
        expect(settings.link).toEqual({ show: true, style: 'button', text: 'Подробнее' });
        expect(settings.lightbox).toEqual({
            mode: 'default',
            use_title: true,
            caption: '',
            show_second_media: false,
            button: { enabled: true, style: 'button', text: 'Подробнее' },
        });
        expect(settings.thumbnail_size).toBe(80);
        expect(settings.show_labels).toBe(true);
        expect(settings.autoplay_interval).toBe(5000);
    });

    it('merges top-level saved settings with defaults', () => {
        const [{ settings }] = withSetup(() => useGallerySettings({ gutter: 40, alignment: 'center' }));

        expect(settings.gutter).toBe(40);
        expect(settings.alignment).toBe('center');
        expect(settings.match_height).toBe(true);
    });

    it('deep merges nested media settings', () => {
        const [{ settings }] = withSetup(() => useGallerySettings({ media: { width: '300', border: 'rounded' } }));

        expect(settings.media).toEqual({ width: '300', height: 'auto', border: 'rounded' });
    });

    it('deep merges content settings', () => {
        const [{ settings }] = withSetup(() => useGallerySettings({ content: { show_title: false, title_size: 'large' } }));

        expect(settings.content).toEqual({
            show_title: false,
            show_content: false,
            title_size: 'large',
        });
    });

    it('deep merges lightbox settings including button', () => {
        const [{ settings }] = withSetup(() => useGallerySettings({
            lightbox: {
                mode: 'enabled',
                use_title: false,
                button: { enabled: false, text: 'Custom' },
            },
        }));

        expect(settings.lightbox?.mode).toBe('enabled');
        expect(settings.lightbox?.use_title).toBe(false);
        expect(settings.lightbox?.caption).toBe('');
        expect(settings.lightbox?.button).toEqual({
            enabled: false,
            style: 'button',
            text: 'Custom',
        });
    });

    it('does not mutate original defaults across instances', () => {
        const [{ settings: s1 }] = withSetup(() => useGallerySettings({ gutter: 99 }));
        const [{ settings: s2 }] = withSetup(() => useGallerySettings());

        expect(s1.gutter).toBe(99);
        expect(s2.gutter).toBe(20);
    });

    it('ignores undefined and null values in saved settings', () => {
        const [{ settings }] = withSetup(() => useGallerySettings(
            { gutter: undefined, match_height: null, alignment: 'right' } as any,
        ));

        expect(settings.gutter).toBe(20);
        expect(settings.match_height).toBe(true);
        expect(settings.alignment).toBe('right');
    });
});
