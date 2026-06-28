<template>
    <div v-if="gallery" class="gallery-renderer" :class="`gallery-align-${settings.alignment || 'left'}`">
        <!-- Сетка (Grid) -->
        <div v-if="gallery.type === 'grid'" class="gallery-grid" :style="gridStyle">
            <div
                v-for="image in gallery.images"
                :key="image.id"
                class="gallery-item overflow-hidden rounded-lg group cursor-pointer"
                :class="mediaBorderClass"
                @click="openLightbox(image)"
            >
                <img
                    :src="image.image_path"
                    :alt="image.alt_text || image.title || 'Изображение'"
                    class="object-cover transition-transform duration-300 group-hover:scale-105 w-full h-auto"
                    :style="imageStyle"
                />
                <div v-if="settings.content?.show_title && image.title" class="mt-2 text-sm font-medium text-gray-800">
                    {{ image.title }}
                </div>
                <div v-if="settings.content?.show_content && image.description" class="mt-1 text-sm text-gray-600">
                    {{ image.description }}
                </div>
                <div v-if="settings.link?.show && image.link" class="mt-2">
                    <a
                        :href="image.link"
                        :target="settings.general?.link_target ? '_blank' : '_self'"
                        class="inline-block"
                        :class="settings.link?.style === 'button' ? 'px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700' : 'text-blue-600 hover:underline'"
                    >
                        {{ settings.link?.text || 'Подробнее' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Слайд-шоу -->
        <div v-else-if="gallery.type === 'slideshow'" class="relative">
            <div class="relative overflow-hidden rounded-lg" :class="mediaBorderClass">
                <div class="relative" :style="{ height: slideshowHeight + 'px' }">
                    <img
                        :src="currentImage?.image_path"
                        :alt="currentImage?.alt_text || currentImage?.title || 'Изображение'"
                        class="w-full h-full object-cover"
                    />
                </div>

                <button
                    v-if="gallery.images.length > 1"
                    @click="prevSlide"
                    class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition"
                >
                    ◀
                </button>
                <button
                    v-if="gallery.images.length > 1"
                    @click="nextSlide"
                    class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition"
                >
                    ▶
                </button>

                <div v-if="gallery.images.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                    <button
                        v-for="(img, index) in gallery.images"
                        :key="img.id"
                        @click="currentSlide = index"
                        class="w-2 h-2 rounded-full transition"
                        :class="currentSlide === index ? 'bg-white' : 'bg-white/50'"
                    />
                </div>
            </div>
        </div>

        <!-- Лайтбокс с навигацией -->
        <div
            v-if="lightboxOpen"
            class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
            @click="closeLightbox"
        >
            <div class="relative max-w-4xl max-h-screen p-4" @click.stop>
                <img
                    :src="lightboxImage?.image_path"
                    :alt="lightboxImage?.alt_text || lightboxImage?.title || 'Изображение'"
                    class="max-w-full max-h-[80vh] object-contain"
                />

                <button
                    @click="closeLightbox"
                    class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 transition opacity-70 hover:opacity-100"
                >
                    ✕
                </button>

                <button
                    v-if="gallery.images.length > 1"
                    @click.stop="lightboxPrev"
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-white opacity-50 hover:opacity-100 transition p-2"
                >
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <button
                    v-if="gallery.images.length > 1"
                    @click.stop="lightboxNext"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white opacity-50 hover:opacity-100 transition p-2"
                >
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <div v-if="gallery.images.length > 1" class="absolute bottom-16 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded">
                    {{ lightboxIndex + 1 }} / {{ gallery.images.length }}
                </div>

                <div v-if="lightboxImage?.title && settings.lightbox?.use_title" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-4 py-2 rounded">
                    {{ lightboxImage.title }}
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-gray-500 text-sm p-4 border border-gray-200 rounded">
        Галерея не найдена
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps<{
    gallery: any;
}>();

const settings = computed(() => props.gallery?.settings || {});

const gutterValue = computed(() => {
    return settings.value.gutter ?? 20;
});

const itemWidth = computed(() => {
    const width = settings.value.media?.width;
    if (width && width !== 'auto') {
        return parseInt(width);
    }
    return 200;
});

const gridStyle = computed(() => {
    const alignment = settings.value.alignment || 'left';
    let justifyContent = 'flex-start';
    if (alignment === 'center') justifyContent = 'center';
    if (alignment === 'right') justifyContent = 'flex-end';

    return {
        display: 'grid',
        gridTemplateColumns: `repeat(auto-fill, ${itemWidth.value}px)`,
        gap: gutterValue.value + 'px',
        justifyContent: justifyContent,
    };
});

const mediaBorderClass = computed(() => {
    const border = settings.value.media?.border || 'none';
    return {
        'rounded-none': border === 'none',
        'rounded-lg': border === 'rounded',
        'rounded-full': border === 'circle',
    };
});

const slideshowHeight = computed(() => {
    const height = settings.value.media?.height;
    return height && height !== 'auto' ? parseInt(height) : 400;
});

const imageStyle = computed(() => {
    const width = settings.value.media?.width;
    const height = settings.value.media?.height;
    const style: any = {};
    if (width && width !== 'auto') style.width = width + 'px';
    if (height && height !== 'auto') style.height = height + 'px';
    return style;
});

// Slideshow
const currentSlide = ref(0);
let autoplayInterval: any = null;

const currentImage = computed(() => {
    return props.gallery?.images?.[currentSlide.value] || null;
});

const nextSlide = () => {
    if (!props.gallery?.images?.length) return;
    currentSlide.value = (currentSlide.value + 1) % props.gallery.images.length;
};

const prevSlide = () => {
    if (!props.gallery?.images?.length) return;
    currentSlide.value = (currentSlide.value - 1 + props.gallery.images.length) % props.gallery.images.length;
};

// Лайтбокс с навигацией
const lightboxOpen = ref(false);
const lightboxImage = ref<any>(null);
const lightboxIndex = ref(0);

const openLightbox = (image: any) => {
    if (settings.value.lightbox?.mode === 'disabled') return;
    lightboxImage.value = image;
    lightboxIndex.value = props.gallery.images.findIndex((img: any) => img.id === image.id);
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
    lightboxImage.value = null;
};

const lightboxPrev = () => {
    if (!props.gallery?.images?.length) return;
    lightboxIndex.value = (lightboxIndex.value - 1 + props.gallery.images.length) % props.gallery.images.length;
    lightboxImage.value = props.gallery.images[lightboxIndex.value];
};

const lightboxNext = () => {
    if (!props.gallery?.images?.length) return;
    lightboxIndex.value = (lightboxIndex.value + 1) % props.gallery.images.length;
    lightboxImage.value = props.gallery.images[lightboxIndex.value];
};

// Обработка клавиш
const handleKeydown = (e: KeyboardEvent) => {
    if (!lightboxOpen.value) return;
    if (e.key === 'ArrowLeft') lightboxPrev();
    if (e.key === 'ArrowRight') lightboxNext();
    if (e.key === 'Escape') closeLightbox();
};

// Автоплей
const startAutoplay = () => {
    if (props.gallery?.type === 'slideshow' && props.gallery?.images?.length > 1) {
        const interval = settings.value.autoplay_interval || 5000;
        autoplayInterval = setInterval(nextSlide, interval);
    }
};

const stopAutoplay = () => {
    if (autoplayInterval) {
        clearInterval(autoplayInterval);
        autoplayInterval = null;
    }
};

onMounted(() => {
    startAutoplay();
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    stopAutoplay();
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.gallery-renderer {
    margin-bottom: 24px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 20px;
}

@media (max-width: 480px) {
    .gallery-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}

@media (min-width: 1440px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

.gallery-renderer img {
    max-width: 100%;
    height: auto;
}
</style>
