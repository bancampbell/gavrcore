import type { EditorPort } from '../../domain/ports/EditorPort';
import { formatHtmlForEditing } from './formatHtmlForEditing';

export class ToggleHtmlModeUseCase {
    constructor(private readonly editorPort: EditorPort) {}

    async getCurrentHtml(): Promise<string> {
        const html = this.editorPort.getHTML();
        return formatHtmlForEditing(html);
    }

    applyHtml(html: string): void {
        if (!this.editorPort.isActive()) return;
        this.editorPort.setContent(html);
    }
}
