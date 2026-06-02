export interface ImageData {
    url: string;
    alt: string;
    title: string;
    width?: string;
    height?: string;
    align?: string;
}

export interface LinkData {
    oldText: string;
    url: string;
    text: string;
    target: string;
    title: string;
}

export interface EditorProps {
    modelValue: string;
}

export interface EditorEmits {
    (e: 'update:modelValue', value: string): void;
    (e: 'openLinkModal'): void;
    (e: 'openImageManager', imageData?: ImageData): void;
    (e: 'editLink', data: LinkData): void;
}
