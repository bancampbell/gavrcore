<template>
    <div ref="containerRef" class="shortcode-renderer"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { createApp, h } from 'vue';
import FormWrapper from '@/themes/default/components/FormWrapper.vue';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';

const props = defineProps<{
    content: string;
    forms: Record<number, any>;
}>();

const containerRef = ref<HTMLElement | null>(null);
const appInstances: any[] = [];

// ===== ЛИНКБОКС =====
const setupLightbox = () => {
    if (!containerRef.value) return;

    const images = containerRef.value.querySelectorAll('.prose img');
    console.log('[ShortcodeRenderer] Setting up lightbox for images:', images.length);

    images.forEach((img: HTMLImageElement) => {
        // Убираем старый обработчик если есть
        if ((img as any).__lightboxHandler) {
            img.removeEventListener('click', (img as any).__lightboxHandler);
        }

        const handler = () => {
            console.log('[ShortcodeRenderer] Image clicked:', img.src);

            // Собираем все изображения в этой секции
            const allImages = containerRef.value?.querySelectorAll('.prose img') || [];
            const imageList: { src: string; alt: string }[] = [];

            allImages.forEach((el: HTMLImageElement) => {
                // Берем src из data-src (если есть) или из src
                const src = el.getAttribute('data-src') || el.src;
                const alt = el.alt || '';
                imageList.push({ src, alt });
            });

            // Находим индекс текущего изображения
            const currentSrc = img.getAttribute('data-src') || img.src;
            const currentIndex = imageList.findIndex(item => item.src === currentSrc);

            console.log('[ShortcodeRenderer] Opening lightbox, index:', currentIndex, 'total:', imageList.length);

            // Открываем линкбокс через глобальный API
            if (window.__globalLightbox && window.__globalLightbox.open) {
                window.__globalLightbox.open(imageList, currentIndex >= 0 ? currentIndex : 0);
            } else {
                console.warn('[ShortcodeRenderer] Lightbox not found');
            }
        };

        img.addEventListener('click', handler);
        (img as any).__lightboxHandler = handler;
    });
};

const renderContent = () => {
    console.log('[ShortcodeRenderer] renderContent called');
    console.log('[ShortcodeRenderer] props.content:', props.content);
    console.log('[ShortcodeRenderer] props.content length:', props.content?.length || 0);

    if (!containerRef.value) {
        console.warn('[ShortcodeRenderer] containerRef is null');
        return;
    }

    appInstances.forEach(app => app.unmount());
    appInstances.length = 0;

    let html = props.content || '';

    // Заменяем шорткоды форм
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

    // Заменяем шорткоды галерей
    html = html.replace(
        /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g,
        (match, galleryId) => {
            return `<div data-gallery-id="${galleryId}" class="gallery-placeholder"></div>`;
        }
    );

    console.log('[ShortcodeRenderer] html after replacements:', html);

    // ОБЕРТКА: добавляем класс prose к содержимому
    containerRef.value.innerHTML = `<div class="prose max-w-none">${html}</div>`;

    console.log('[ShortcodeRenderer] containerRef.innerHTML set, length:', containerRef.value.innerHTML.length);

    // Рендерим формы через FormWrapper
    const formPlaceholders = containerRef.value.querySelectorAll('.form-placeholder');
    console.log('[ShortcodeRenderer] formPlaceholders found:', formPlaceholders.length);
    formPlaceholders.forEach((placeholder) => {
        const formId = placeholder.getAttribute('data-form-id');
        if (!formId) return;

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
    console.log('[ShortcodeRenderer] galleryPlaceholders found:', galleryPlaceholders.length);
    galleryPlaceholders.forEach((placeholder) => {
        const galleryId = placeholder.getAttribute('data-gallery-id');
        if (!galleryId) return;

        const container = document.createElement('div');
        container.className = 'gallery-container';
        placeholder.parentNode?.replaceChild(container, placeholder);

        import('axios').then(({ default: axios }) => {
            axios.get(`/api/galleries/${galleryId}`)
                .then(response => {
                    const app = createApp({
                        render() {
                            return h(GalleryRenderer, { gallery: response.data });
                        }
                    });
                    app.mount(container);
                    appInstances.push(app);
                })
                .catch(error => {
                    console.error(`Gallery ${galleryId} not found`);
                    container.innerHTML = `<div class="text-red-500 text-sm">⚠️ Галерея не найдена</div>`;
                });
        });
    });

    // Настраиваем линкбокс после рендера
    nextTick(() => {
        setupLightbox();
    });
};

onMounted(() => {
    console.log('[ShortcodeRenderer] onMounted');
    renderContent();
});

watch(() => props.content, (newVal, oldVal) => {
    console.log('[ShortcodeRenderer] watch props.content changed');
    console.log('[ShortcodeRenderer] oldVal:', oldVal);
    console.log('[ShortcodeRenderer] newVal:', newVal);
    renderContent();
});

onBeforeUnmount(() => {
    console.log('[ShortcodeRenderer] onBeforeUnmount, cleaning up');
    appInstances.forEach(app => app.unmount());
});
</script>

<style scoped>
.shortcode-renderer {
    width: 100%;
}
</style>
