<template>
    <div ref="containerRef" class="shortcode-renderer"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { createApp, h } from 'vue';
import FormWrapper from '@/themes/default/components/FormWrapper.vue';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';

const props = defineProps<{
    content: string;
    forms: Record<number, any>;
}>();

const containerRef = ref<HTMLElement | null>(null);
const appInstances: any[] = [];

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

.shortcode-renderer .prose {
    max-width: none;
}

.shortcode-renderer .prose img {
    max-width: 100%;
    height: auto;
    cursor: pointer;
}

.shortcode-renderer .prose a[href$=".jpg"],
.shortcode-renderer .prose a[href$=".jpeg"],
.shortcode-renderer .prose a[href$=".png"],
.shortcode-renderer .prose a[href$=".gif"],
.shortcode-renderer .prose a[href$=".webp"],
.shortcode-renderer .prose a[href$=".svg"] {
    cursor: pointer;
}

/* Стили для заголовков */
.shortcode-renderer .prose h1 {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.shortcode-renderer .prose h2 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.shortcode-renderer .prose h3 {
    font-size: 1.25rem;
    font-weight: bold;
    margin-top: 0.75rem;
    margin-bottom: 0.5rem;
}
.shortcode-renderer .prose p {
    margin-bottom: 0.5rem;
}
.shortcode-renderer .prose ul,
.shortcode-renderer .prose ol {
    padding-left: 1.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
.shortcode-renderer .prose li {
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}
</style>
