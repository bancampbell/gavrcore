import { FilePath } from '../values/FilePath';

export type MediaType = 'folder' | 'file';

export interface MediaItemProps {
    name: string;
    path: string | FilePath;
    type: MediaType;
    size?: number;
    mime_type?: string;
    modified?: number;
}

export interface MediaItemResponse {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size?: number;
    mime_type?: string;
    modified?: number;
}

export class MediaItem {
    public readonly name: string;
    public readonly path: FilePath;
    public readonly type: MediaType;
    public readonly size: number | null;
    public readonly mimeType: string | null;
    public readonly modified: number | null;

    private constructor(props: MediaItemProps) {
        this.name = props.name;
        this.path = props.path instanceof FilePath ? props.path : FilePath.create(props.path);
        this.type = props.type;
        this.size = props.size ?? null;
        this.mimeType = props.mime_type ?? null;
        this.modified = props.modified ?? null;
    }

    static create(props: MediaItemProps): MediaItem {
        return new MediaItem(props);
    }

    static fromResponse(data: MediaItemResponse): MediaItem {
        return new MediaItem({
            name: data.name || '',
            path: data.path || '',
            type: data.type === 'folder' ? 'folder' : 'file',
            size: data.size,
            mime_type: data.mime_type,
            modified: data.modified,
        });
    }

    isFolder(): boolean {
        return this.type === 'folder';
    }

    isFile(): boolean {
        return this.type === 'file';
    }

    getExtension(): string {
        return this.path.getExtension();
    }

    getSizeFormatted(): string {
        if (this.size === null || this.size === 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(this.size) / Math.log(1024));
        const size = (this.size / Math.pow(1024, i)).toFixed(1);
        return `${size} ${units[i]}`;
    }

    getParentPath(): string {
        return this.path.getParent().toString();
    }

    isImage(): boolean {
        if (!this.isFile()) return false;
        const ext = this.getExtension();
        return ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'].includes(ext);
    }

    isVideo(): boolean {
        if (!this.isFile()) return false;
        const ext = this.getExtension();
        return ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'].includes(ext);
    }

    getUrl(): string {
        return this.path.getStorageUrl();
    }

    getPathString(): string {
        return this.path.toString();
    }
}
