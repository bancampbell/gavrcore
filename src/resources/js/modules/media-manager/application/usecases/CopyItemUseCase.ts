import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import { FilePath } from '../../domain/values/FilePath';

export class CopyItemUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(path: string | FilePath): Promise<void> {
        const pathStr = path instanceof FilePath ? path.toString() : path;
        await this.repository.copyItem(pathStr);
    }
}
