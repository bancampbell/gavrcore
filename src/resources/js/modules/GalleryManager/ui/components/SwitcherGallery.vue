<template>
    <div class="switcher-gallery">
        <div class="flex justify-center">
            <div
                class="switcher-main overflow-hidden bg-gray-100 cursor-pointer"
                :class="mediaBorderClass"
                :style="containerStyle"
                @click="openLightbox"
            >
                <img
                    v-if="currentImage"
                    :src="currentImage.image_path"
                    :alt="currentImage.alt_text || currentImage.title || 'Изображение'"
                    :style="imageStyle"
                    loading="lazy"
                />
            </div>
        </div>

        <div class="switcher-thumbnails">
            <div
                v-for="(image, index) in gallery.images"
                :key="image.id"
                @click.stop="selectImage(index)"
                class="switcher-thumbnail"
                :class="[
                    currentIndex === index ? 'active' : '',
                    mediaBorderClass
                ]"
                :style="{ width: thumbnailSize + 'px', height: thumbnailSize + 'px' }"
            >
                <img
                    :src="image.image_path"
                    :alt="image.alt_text || image.title || 'Изображение'"
                    loading="lazy"
                />
                <div v-if="settings.show_labels && image.title" class="label">
                    {{ image.title }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import '../styles/gallery.css';
import type { Gallery, GalleryImage, GallerySettings } from '../types';

const props = defineProps<{
    gallery: Gallery;
}>();

const emit = defineEmits<{
    (e: 'openLightbox', image: GalleryImage): void;
}>();

const settings = computed<GallerySettings>(() => props.gallery?.settings || {});
const currentIndex = ref(0);

const thumbnailSize = computed(() => settings.value.thumbnail_size || 80);

const mediaBorderClass = computed(() => {
    const border = settings.value.media?.border || 'none';
    return {
        'rounded-none': border === 'none',
        'rounded-lg': border === 'rounded',
        'rounded-full': border === 'circle',
    };
});

const containerStyle = computed(() => {
    const width = settings.value.media?.width;
    const height = settings.value.media?.height;
    const style: Record<string, string> = {};
    if (width && width !== 'auto') style.width = width + 'px';
    else style.width = '100%';
    if (height && height !== 'auto') style.height = height + 'px';
    return style;
});

const imageStyle = computed(() => ({
    width: '100%',
    height: '100%',
    objectFit: 'cover' as const,
}));

const currentImage = computed<GalleryImage | null>(() => props.gallery?.images?.[currentIndex.value] || null);

const selectImage = (index: number) => { currentIndex.value = index; };

const openLightbox = () => {
    if (currentImage.value) emit('openLightbox', currentImage.value);
};
</script>
