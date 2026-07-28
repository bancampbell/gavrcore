import { ref, type Ref } from 'vue';

export interface LightboxImage {
    src: string;
    alt: string;
}

export class LightboxService {
    public isOpen: Ref<boolean> = ref(false);
    public images: Ref<LightboxImage[]> = ref([]);
    public currentIndex: Ref<number> = ref(0);

    open(imageList: LightboxImage[], index: number = 0): void {
        this.images.value = imageList;
        this.currentIndex.value = index;
        this.isOpen.value = true;
        document.body.style.overflow = 'hidden';
    }

    close(): void {
        this.isOpen.value = false;
        document.body.style.overflow = '';
    }

    prev(): void {
        if (this.images.value.length === 0) return;
        this.currentIndex.value = (this.currentIndex.value - 1 + this.images.value.length) % this.images.value.length;
    }

    next(): void {
        if (this.images.value.length === 0) return;
        this.currentIndex.value = (this.currentIndex.value + 1) % this.images.value.length;
    }

    get currentImage(): LightboxImage | null {
        return this.images.value[this.currentIndex.value] || null;
    }
}

export const lightboxService = new LightboxService();
