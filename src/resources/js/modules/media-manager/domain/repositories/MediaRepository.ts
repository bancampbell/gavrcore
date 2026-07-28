import type { MediaItem } from '../entities/MediaItem';

export interface MediaRepository {
    loadFolders(): Promise<MediaItem[]>;
    loadContents(path: string): Promise<MediaItem[]>;
    createFolder(name: string, path: string): Promise<void>;
    renameItem(oldPath: string, newName: string): Promise<void>;
    deleteItem(path: string): Promise<void>;
    deleteItems(paths: string[]): Promise<void>;
    copyItem(path: string): Promise<void>;
    uploadFile(files: FileList, path: string, onProgress?: (percent: number) => void): Promise<void>;
}
