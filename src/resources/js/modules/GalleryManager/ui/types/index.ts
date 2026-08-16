export type GalleryType = 'grid' | 'slideshow' | 'slider' | 'switcher';

export interface Gallery {
    id: number;
    title: string;
    type: GalleryType;
    status: boolean;
    settings: GallerySettings;
    images: GalleryImage[];
    images_count?: number;
    created_at: string;
    updated_at: string;
}

export interface GalleryImage {
    id: number;
    gallery_id: number;
    image_path: string;
    title: string | null;
    description: string | null;
    alt_text: string | null;
    link: string | null;
    ordering: number;
    status: boolean;
}

export interface GallerySettings {
    gutter?: number;
    match_height?: boolean;
    alignment?: 'left' | 'center' | 'right';
    media?: {
        width?: string;
        height?: string;
        border?: 'none' | 'rounded' | 'circle';
    };
    content?: {
        show_title?: boolean;
        show_content?: boolean;
        title_size?: 'default' | 'small' | 'large';
    };
    link?: {
        show?: boolean;
        style?: 'button' | 'text';
        text?: string;
    };
    lightbox?: {
        mode?: 'default' | 'enabled' | 'disabled';
        use_title?: boolean;
        caption?: string;
        show_second_media?: boolean;
        button?: {
            enabled?: boolean;
            style?: 'button' | 'text';
            text?: string;
        };
    };
    thumbnail_size?: number;
    show_labels?: boolean;
    autoplay_interval?: number;
}

export interface GalleryFilters {
    search?: string;
    type?: string;
    status?: string;
}

export interface GalleryListItem {
    id: number;
    title: string;
    type: GalleryType;
    status: boolean;
    images_count?: number;
    created_at: string;
}
