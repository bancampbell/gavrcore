import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import { MediaItem, type MediaItemResponse } from '../../domain/entities/MediaItem';
import { httpClient } from '@/services/HttpClient';
import { FileValidator } from '../../domain/services/FileValidator';

export class ApiMediaRepository implements MediaRepository {
    private readonly baseUrl = '/admin/media';

    async loadFolders(): Promise<MediaItem[]> {
        const data = await httpClient.get<MediaItemResponse[]>(`${this.baseUrl}/folders`);
        return data.map(item => MediaItem.fromResponse(item));
    }

    async loadContents(path: string): Promise<MediaItem[]> {
        const data = await httpClient.get<MediaItemResponse[]>(`${this.baseUrl}/contents`, {
            params: { path },
        });
        return data.map(item => MediaItem.fromResponse(item));
    }

    async createFolder(name: string, path: string): Promise<void> {
        await httpClient.post(`${this.baseUrl}/folder`, { name, path });
    }

    async renameItem(oldPath: string, newName: string): Promise<void> {
        await httpClient.post(`${this.baseUrl}/rename`, { old_path: oldPath, new_name: newName });
    }

    async deleteItem(path: string): Promise<void> {
        await httpClient.delete(`${this.baseUrl}/item`, {
            data: { path },
            headers: { 'Content-Type': 'application/json' },
        });
    }

    async deleteItems(paths: string[]): Promise<void> {
        const stringPaths = paths.map(p => typeof p === 'string' ? p : String(p));
        await httpClient.delete(`${this.baseUrl}/items`, {
            data: { paths: stringPaths },
            headers: { 'Content-Type': 'application/json' },
        });
    }

    async copyItem(path: string): Promise<void> {
        await httpClient.post(`${this.baseUrl}/copy`, { path });
    }

    async uploadFile(files: FileList, path: string, onProgress?: (percent: number) => void): Promise<void> {
        const fileArray = Array.from(files);
        const validation = FileValidator.validateFiles(fileArray);
        if (!validation.valid) {
            throw new Error(`Validation failed: ${validation.errors.join('; ')}`);
        }

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }
        formData.append('path', path);

        await httpClient.upload(`${this.baseUrl}/upload`, formData, onProgress);
    }
}
