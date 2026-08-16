import { reactive } from 'vue';
import type { GallerySettings } from '../types';

const defaultSettings: GallerySettings = {
    gutter: 20,
    match_height: true,
    alignment: 'left',
    media: { width: 'auto', height: 'auto', border: 'none' },
    content: { show_title: true, show_content: false, title_size: 'default' },
    link: { show: true, style: 'button', text: 'Подробнее' },
    lightbox: {
        mode: 'default',
        use_title: true,
        caption: '',
        show_second_media: false,
        button: { enabled: true, style: 'button', text: 'Подробнее' }
    },
    thumbnail_size: 80,
    show_labels: true,
    autoplay_interval: 5000,
};

export function useGallerySettings(savedSettings?: GallerySettings) {
    const settings = reactive<GallerySettings>(mergeDeep(
        JSON.parse(JSON.stringify(defaultSettings)),
        savedSettings || {}
    ));

    return { settings };
}

function mergeDeep<T extends Record<string, any>>(target: T, source: Partial<T>): T {
    const result = JSON.parse(JSON.stringify(target));
    for (const key in source) {
        if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
            if (!result[key]) result[key] = {};
            result[key] = mergeDeep(result[key], source[key] as any);
        } else if (source[key] !== undefined && source[key] !== null) {
            result[key] = source[key];
        }
    }
    return result;
}
