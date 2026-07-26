import type { EditorPort } from '../../domain/ports/EditorPort';
import type { LinkPort } from '../../domain/ports/LinkPort';
import type { LinkData } from '../../domain/values/LinkData';

export class UpdateLinkUseCase {
    constructor(
        private readonly editorPort: EditorPort,
        private readonly linkPort: LinkPort,
    ) {}

    execute(oldText: string, newData: LinkData): void {
        if (!this.editorPort.isActive()) return;

        this.linkPort.updateLink(oldText, newData);
    }
}
