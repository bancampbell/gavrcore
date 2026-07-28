import type { MediaRepository } from '../../domain/repositories/MediaRepository';
import { FilePath } from '../../domain/values/FilePath';
import { FileValidator } from '../../domain/services/FileValidator';

export class UploadFileUseCase {
    constructor(private readonly repository: MediaRepository) {}

    async execute(
        files: FileList,
        path: string | FilePath,
        onProgress?: (percent: number) => void,
    ): Promise<void> {
        const pathStr = path instanceof FilePath ? path.toString() : path;

        // Валидация файлов
        const fileArray = Array.from(files);
        const validation = FileValidator.validateFiles(fileArray);
        if (!validation.valid) {
            throw new Error(`Validation failed: ${validation.errors.join('; ')}`);
        }

        await this.repository.uploadFile(files, pathStr, onProgress);
    }
}
