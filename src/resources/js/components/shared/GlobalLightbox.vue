<template>
    <div
        v-if="lightboxService.isOpen.value"
        class="fixed inset-0 z-[9999] bg-black/85 flex items-center justify-center"
        @click="close"
    >
        <div class="relative max-w-4xl max-h-screen p-4" @click.stop>
            <div class="relative inline-block">
                <img
                    v-if="currentImage"
                    :src="currentImage.src"
                    :alt="currentImage.alt"
                    class="max-w-full max-h-[80vh] object-contain border-[3px] border-white/40 rounded-lg shadow-2xl"
                />

                <button
                    @click="close"
                    class="!absolute !-top-1 !-right-1 !text-white !text-4xl hover:!text-gray-300 !transition-all !duration-200 !opacity-100 hover:!opacity-80 !border-none !bg-transparent hover:!bg-transparent !m-0 !p-0 !leading-none [text-shadow:_0_0_10px_rgba(0,0,0,0.8),_0_0_20px_rgba(0,0,0,0.6)]"
                    style="position: absolute !important; top: 4px !important; right: 4px !important;"
                >
                    ✕
                </button>
            </div>

            <button
                v-if="lightboxService.images.value.length > 1"
                @click.stop="prev"
                class="!absolute !left-8 !top-1/2 !-translate-y-1/2 !text-white hover:!text-gray-300 !transition-all !duration-200 !opacity-100 hover:!opacity-80 !p-2 !border-none !bg-transparent hover:!bg-transparent [text-shadow:_0_0_10px_rgba(0,0,0,0.8),_0_0_20px_rgba(0,0,0,0.6)]"
            >
                <svg class="!w-10 !h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button
                v-if="lightboxService.images.value.length > 1"
                @click.stop="next"
                class="!absolute !right-8 !top-1/2 !-translate-y-1/2 !text-white hover:!text-gray-300 !transition-all !duration-200 !opacity-100 hover:!opacity-80 !p-2 !border-none !bg-transparent hover:!bg-transparent [text-shadow:_0_0_10px_rgba(0,0,0,0.8),_0_0_20px_rgba(0,0,0,0.6)]"
            >
                <svg class="!w-10 !h-10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <div v-if="lightboxService.images.value.length > 1" class="absolute bottom-16 left-1/2 -translate-x-1/2 text-white text-sm bg-black/50 px-3 py-1 rounded-full backdrop-blur-sm">
                {{ lightboxService.currentIndex.value + 1 }} / {{ lightboxService.images.value.length }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { lightboxService } from '@/services/LightboxService';

console.log('[GlobalLightbox] setup, lightboxService:', lightboxService);

const currentImage = computed(() => lightboxService.currentImage);

const close = () => lightboxService.close();
const prev = () => lightboxService.prev();
const next = () => lightboxService.next();

const handleKeydown = (e: KeyboardEvent) => {
    if (!lightboxService.isOpen.value) return;
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'Escape') close();
};

onMounted(() => {
    console.log('[GlobalLightbox] mounted');
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>
