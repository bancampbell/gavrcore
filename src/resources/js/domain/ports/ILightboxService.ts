import type { Ref } from 'vue';

export interface LightboxImage {
    src: string;
    alt: string;
}

export interface ILightboxService {
    isOpen: Ref<boolean>;
    images: Ref<LightboxImage[]>;
    currentIndex: Ref<number>;
    currentImage: LightboxImage | null;
    open(imageList: LightboxImage[], index?: number): void;
    close(): void;
    prev(): void;
    next(): void;
}
