import type { EditorPort } from '../../domain/ports/EditorPort';

export class DeleteNodeUseCase {
    constructor(private readonly editorPort: EditorPort) {}

    execute(pos: number): void {
        if (!this.editorPort.isActive()) return;

        const node = this.editorPort.getNodeAt(pos);
        if (!node) return;

        this.editorPort.setTextSelection(pos, pos + 1);
        this.editorPort.insertContent('');
    }
}
