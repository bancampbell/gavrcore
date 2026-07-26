import type { EditorPort } from '../../domain/ports/EditorPort';
import type { ImagePort } from '../../domain/ports/ImagePort';
import type { ImageData } from '../../domain/values/ImageData';

export class InsertImageUseCase {
    constructor(
        private readonly editorPort: EditorPort,
        private readonly imagePort: ImagePort,
    ) {}

    execute(data: ImageData, position?: number): void {
        if (!this.editorPort.isActive()) return;

        const pos = position ?? this.editorPort.getSelection().from;
        this.imagePort.insertImage(data, pos);
    }
}
