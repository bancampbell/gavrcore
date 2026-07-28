import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import { FilePath } from '../../domain/values/FilePath';

export class RenameItemUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(oldPath: string | FilePath, newName: string): Promise<void> {
        const pathStr = oldPath instanceof FilePath ? oldPath.toString() : oldPath;
        await this.repository.renameItem(pathStr, newName);
    }
}
