import type { EditorPort } from '../../domain/ports/EditorPort';

export class ToggleHtmlModeUseCase {
    constructor(private readonly editorPort: EditorPort) {}

    getCurrentHtml(): string {
        return this.editorPort.getHTML();
    }

    applyHtml(html: string): void {
        if (!this.editorPort.isActive()) return;
        this.editorPort.setContent(html);
    }
}
