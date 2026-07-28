import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import type { MediaItem } from '../../domain/entities/MediaItem';
import { FilePath } from '../../domain/values/FilePath';

export class LoadContentsUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(path: string | FilePath): Promise<MediaItem[]> {
        const pathStr = path instanceof FilePath ? path.toString() : path;
        return await this.repository.loadContents(pathStr);
    }
}
