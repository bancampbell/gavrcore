import type { ImageData } from '../values/ImageData';

export interface ImagePort {
    insertImage(data: ImageData, position?: number): void;
    updateImage(pos: number, data: ImageData): void;
    getImageAt(pos: number): ImageData | null;
    findImagePosition(url: string): number;
}
