import type { EditorPort } from '../../domain/ports/EditorPort';
import type { LinkPort } from '../../domain/ports/LinkPort';
import type { LinkData } from '../../domain/values/LinkData';

export class InsertLinkUseCase {
    constructor(
        private readonly editorPort: EditorPort,
        private readonly linkPort: LinkPort,
    ) {}

    execute(data: LinkData): void {
        if (!this.editorPort.isActive()) return;

        const selection = this.editorPort.getSelection();
        this.linkPort.insertLink(data, { from: selection.from, to: selection.to });
    }
}
