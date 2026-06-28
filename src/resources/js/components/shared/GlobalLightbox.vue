<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-[9999] bg-black/90 flex items-center justify-center"
        @click="close"
    >
        <div class="relative max-w-4xl max-h-screen p-4" @click.stop>
            <img
                :src="currentImage"
                :alt="currentAlt"
                class="max-w-full max-h-[80vh] object-contain"
            />
            <button
                @click="close"
                class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 transition opacity-70 hover:opacity-100"
            >
                ✕
            </button>
            <button
                v-if="images.length > 1"
                @click.stop="prev"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-white opacity-50 hover:opacity-100 transition p-2"
            >
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button
                v-if="images.length > 1"
                @click.stop="next"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white opacity-50 hover:opacity-100 transition p-2"
            >
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div v-if="images.length > 1" class="absolute bottom-16 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const isOpen = ref(false);
const images = ref<{ src: string; alt: string }[]>([]);
const currentIndex = ref(0);

const currentImage = computed(() => images.value[currentIndex.value]?.src || '');
const currentAlt = computed(() => images.value[currentIndex.value]?.alt || '');

const open = (imageList: { src: string; alt: string }[], index: number = 0) => {
    images.value = imageList;
    currentIndex.value = index;
    isOpen.value = true;
    document.body.style.overflow = 'hidden';
};

const close = () => {
    isOpen.value = false;
    document.body.style.overflow = '';
};

const prev = () => {
    if (images.value.length === 0) return;
    currentIndex.value = (currentIndex.value - 1 + images.value.length) % images.value.length;
};

const next = () => {
    if (images.value.length === 0) return;
    currentIndex.value = (currentIndex.value + 1) % images.value.length;
};

// Обработка клавиш
const handleKeydown = (e: KeyboardEvent) => {
    if (!isOpen.value) return;
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'Escape') close();
};

watch(isOpen, (newVal) => {
    if (newVal) {
        window.addEventListener('keydown', handleKeydown);
    } else {
        window.removeEventListener('keydown', handleKeydown);
    }
});

// Глобально доступные методы
if (typeof window !== 'undefined') {
    window.__globalLightbox = {
        open,
        close,
        prev,
        next,
    };
}
</script>
