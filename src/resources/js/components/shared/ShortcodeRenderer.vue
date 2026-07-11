<template>
    <div ref="containerRef" class="shortcode-renderer"></div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { createApp, h } from 'vue';
import FormRenderer from '@/components/Forms/FormRenderer.vue';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';

const props = defineProps<{
    content: string;
    forms: Record<number, any>;
}>();

const containerRef = ref<HTMLElement | null>(null);
const appInstances: any[] = [];

const renderContent = () => {
    if (!containerRef.value) return;

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

    containerRef.value.innerHTML = html;

    // Рендерим формы
    const formPlaceholders = containerRef.value.querySelectorAll('.form-placeholder');
    formPlaceholders.forEach((placeholder) => {
        const formId = placeholder.getAttribute('data-form-id');
        if (!formId) return;

        // Ищем родительский элемент с центрированием
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

        // Создаем контейнер
        const container = document.createElement('div');
        container.className = 'form-container';

        // Если нужно центрировать
        if (centered) {
            container.style.display = 'flex';
            container.style.justifyContent = 'center';
            container.style.width = '100%';
            container.style.textAlign = 'center';
        }

        // Заменяем placeholder на контейнер
        placeholder.replaceWith(container);

        // Монтируем форму
        const app = createApp({
            render() {
                return h(FormRenderer, {
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
    renderContent();
});

watch(() => props.content, () => {
    renderContent();
});

onBeforeUnmount(() => {
    appInstances.forEach(app => app.unmount());
});
</script>
