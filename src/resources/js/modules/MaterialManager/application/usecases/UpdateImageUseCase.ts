import type { EditorPort } from '../../domain/ports/EditorPort';
import type { ImagePort } from '../../domain/ports/ImagePort';
import type { ImageData } from '../../domain/values/ImageData';

export class UpdateImageUseCase {
    constructor(
        private readonly editorPort: EditorPort,
        private readonly imagePort: ImagePort,
    ) {}

    execute(oldUrl: string, newData: ImageData): void {
        if (!this.editorPort.isActive()) return;

        const pos = this.imagePort.findImagePosition(oldUrl);
        if (pos === -1) return;

        this.imagePort.updateImage(pos, newData);
    }
}
