import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import type { MediaItem } from '../../domain/entities/MediaItem';

export class LoadFoldersUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(): Promise<MediaItem[]> {
        return await this.repository.loadFolders();
    }
}
