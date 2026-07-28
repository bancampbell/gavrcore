import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import { FilePath } from '../../domain/values/FilePath';

export class DeleteItemsUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(paths: (string | FilePath)[]): Promise<void> {
        const pathStrings = paths.map(p => p instanceof FilePath ? p.toString() : p);
        await this.repository.deleteItems(pathStrings);
    }
}
