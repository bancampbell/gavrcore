<template>
    <div ref="containerRef" class="shortcode-renderer"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { createApp, h } from 'vue';
import FormWrapper from '@/themes/default/components/FormWrapper.vue';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';
import { lightboxService } from '@/services/LightboxService';
import axios from 'axios';
import DOMPurify from 'dompurify';

const props = defineProps<{
    content: string;
    forms: Record<number, any>;
}>();

const containerRef = ref<HTMLElement | null>(null);
const appInstances: any[] = [];
const galleryCache = new Map<string, any>();

const setupLightbox = () => {
    if (!containerRef.value) return;

    // Обработка изображений <img>
    const images = containerRef.value.querySelectorAll('.prose img');

    images.forEach((img: HTMLImageElement) => {
        if ((img as any).__lightboxHandler) {
            img.removeEventListener('click', (img as any).__lightboxHandler);
        }

        const handler = () => {
            const allImages = containerRef.value?.querySelectorAll('.prose img') || [];
            const imageList: { src: string; alt: string }[] = [];

            allImages.forEach((el: HTMLImageElement) => {
                const src = el.getAttribute('data-src') || el.src;
                const alt = el.alt || '';
                imageList.push({ src, alt });
            });

            const currentSrc = img.getAttribute('data-src') || img.src;
            const currentIndex = imageList.findIndex(item => item.src === currentSrc);

            lightboxService.open(imageList, currentIndex >= 0 ? currentIndex : 0);
        };

        img.addEventListener('click', handler);
        (img as any).__lightboxHandler = handler;
    });

    // Обработка ссылок на изображения
    const imageLinks = containerRef.value.querySelectorAll(
        '.prose a[href$=".jpg"], .prose a[href$=".jpeg"], .prose a[href$=".png"], .prose a[href$=".gif"], .prose a[href$=".webp"], .prose a[href$=".svg"]'
    );

    imageLinks.forEach((link: HTMLAnchorElement) => {
        if ((link as any).__lightboxHandler) {
            link.removeEventListener('click', (link as any).__lightboxHandler);
        }

        const handler = (e: Event) => {
            e.preventDefault();
            lightboxService.open([{ src: link.href, alt: link.textContent || '' }], 0);
        };

        link.addEventListener('click', handler);
        (link as any).__lightboxHandler = handler;
    });
};

const renderContent = () => {
    if (!containerRef.value) {
        console.warn('[ShortcodeRenderer] containerRef is null');
        return;
    }

    appInstances.forEach(app => app.unmount());
    appInstances.length = 0;

    let html = props.content || '';

    html = html.replace(
        /\[form\s+id="(\d+)"(?:\s+([^\]]*))?\]/g,
        (match, formId) => {
            const form = props.forms[formId];
            if (!form) {
                return `<div class="text-red-500 text-sm">⚠️ Форма не найдена</div>`;
            }
            return `<div data-form-id="${formId}" class="form-placeholder"></div>`;
        }
    );

    html = html.replace(
        /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g,
        (match, galleryId) => {
            return `<div data-gallery-id="${galleryId}" class="gallery-placeholder"></div>`;
        }
    );

    const sanitizedHtml = DOMPurify.sanitize(html, {
        ALLOWED_TAGS: [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            'a', 'img', 'div', 'span',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'section', 'article', 'header', 'footer', 'nav',
        ],
        ALLOWED_ATTR: ['href', 'target', 'title', 'src', 'alt', 'width', 'height', 'style', 'class', 'id', 'data-form-id', 'data-gallery-id'],
        ALLOW_DATA_ATTR: true,
        ADD_URI_SAFE_ATTR: ['src', 'href'],
    });

    containerRef.value.innerHTML = `<div class="prose max-w-none">${sanitizedHtml}</div>`;

    // Рендерим формы
    const formPlaceholders = containerRef.value.querySelectorAll('.form-placeholder');
    formPlaceholders.forEach((placeholder) => {
        const formId = placeholder.getAttribute('data-form-id');
        if (!formId) return;

        if (!/^\d+$/.test(formId)) {
            placeholder.innerHTML = `<div class="text-red-500 text-sm">⚠️ Некорректный ID формы</div>`;
            return;
        }

        let centered = false;
        let parent = placeholder.parentElement;
        while (parent) {
            const style = parent.getAttribute('style') || '';
            const className = parent.className || '';
            if (style.includes('text-align: center') ||
                style.includes('text-align:center') ||
                className.includes('text-center') ||
                parent.tagName === 'CENTER') {
                centered = true;
                break;
            }
            parent = parent.parentElement;
        }

        const container = document.createElement('div');
        container.className = 'form-container';

        if (centered) {
            container.style.display = 'flex';
            container.style.justifyContent = 'center';
            container.style.width = '100%';
            container.style.textAlign = 'center';
        }

        placeholder.replaceWith(container);

        const app = createApp({
            render() {
                return h(FormWrapper, {
                    formId: Number(formId),
                    centered: centered
                });
            }
        });
        app.mount(container);
        appInstances.push(app);
    });

    // Рендерим галереи
    const galleryPlaceholders = containerRef.value.querySelectorAll('.gallery-placeholder');
    const galleryIds: string[] = [];
    const placeholderMap = new Map<string, Element>();

    galleryPlaceholders.forEach((placeholder) => {
        const galleryId = placeholder.getAttribute('data-gallery-id');
        if (galleryId && /^\d+$/.test(galleryId)) {
            galleryIds.push(galleryId);
            placeholderMap.set(galleryId, placeholder);
        } else if (galleryId) {
            placeholder.innerHTML = `<div class="text-red-500 text-sm">⚠️ Некорректный ID галереи</div>`;
        }
    });

    if (galleryIds.length > 0) {
        const fetchPromises = galleryIds.map((galleryId) => {
            if (galleryCache.has(galleryId)) {
                return Promise.resolve({ galleryId, data: galleryCache.get(galleryId) });
            }
            return axios.get(`/api/galleries/${galleryId}`)
                .then(response => {
                    galleryCache.set(galleryId, response.data);
                    return { galleryId, data: response.data };
                })
                .catch(() => {
                    return { galleryId, data: null };
                });
        });

        Promise.allSettled(fetchPromises).then((results) => {
            results.forEach((result) => {
                const galleryId = (result as any).value?.galleryId || (result as any).reason?.galleryId;
                if (!galleryId) return;

                const placeholder = placeholderMap.get(galleryId);
                if (!placeholder) return;

                const container = document.createElement('div');
                container.className = 'gallery-container';
                placeholder.parentNode?.replaceChild(container, placeholder);

                if (result.status === 'fulfilled' && result.value?.data) {
                    const app = createApp({
                        render() {
                            return h(GalleryRenderer, { gallery: result.value.data });
                        }
                    });
                    app.mount(container);
                    appInstances.push(app);
                } else {
                    container.innerHTML = `<div class="text-red-500 text-sm">⚠️ Галерея не найдена</div>`;
                }
            });
        });
    }

    nextTick(() => {
        setupLightbox();
    });
};

onMounted(() => {
    renderContent();
});

watch(() => props.content, () => {
    renderContent();
});

onBeforeUnmount(() => {
    appInstances.forEach(app => app.unmount());
    galleryCache.clear();
});
</script>

<style scoped>
.shortcode-renderer {
    width: 100%;
}
</style>
