import type { Editor } from '@tiptap/core';
import type { EditorPort } from '../../domain/ports/EditorPort';
import { Selection } from '../../domain/values/Selection';

export class TiptapEditorAdapter implements EditorPort {
    private editor: Editor | null = null;
    private updateCallbacks: Array<(html: string) => void> = [];

    init(editor: Editor): void {
        this.editor = editor;
    }

    getHTML(): string {
        return this.editor?.getHTML() ?? '';
    }

    setContent(content: string): void {
        this.editor?.commands.setContent(content);
    }

    insertContent(html: string, position?: number): void {
        if (!this.editor) return;
        const pos = position ?? this.editor.state.selection.from;
        this.editor.chain().focus().setTextSelection(pos).insertContent(html).run();
    }

    getSelection(): Selection {
        if (!this.editor) return new Selection(0, 0, '');
        const { from, to } = this.editor.state.selection;
        const text = this.editor.state.doc.textBetween(from, to);
        return new Selection(from, to, text);
    }

    setTextSelection(from: number, to?: number): void {
        this.editor?.commands.setTextSelection({ from, to: to ?? from });
    }

    getNodeAt(pos: number): { type: string; attrs: Record<string, any> } | null {
        if (!this.editor) return null;
        const node = this.editor.state.doc.nodeAt(pos);
        if (!node) return null;
        return { type: node.type.name, attrs: { ...node.attrs } };
    }

    updateNode(pos: number, attrs: Record<string, any>): void {
        if (!this.editor) return;
        const tr = this.editor.state.tr.setNodeMarkup(pos, undefined, attrs);
        this.editor.view.dispatch(tr);
    }

    deleteNode(pos: number): void {
        if (!this.editor) return;
        const node = this.editor.state.doc.nodeAt(pos);
        if (!node) return;
        const tr = this.editor.state.tr.delete(pos, pos + node.nodeSize);
        this.editor.view.dispatch(tr);
    }

    isActive(): boolean {
        return this.editor !== null && !this.editor.isDestroyed;
    }

    onUpdate(callback: (html: string) => void): () => void {
        this.updateCallbacks.push(callback);
        return () => {
            this.updateCallbacks = this.updateCallbacks.filter(cb => cb !== callback);
        };
    }

    triggerUpdate(html: string): void {
        this.updateCallbacks.forEach(cb => cb(html));
    }

    getEditor(): Editor | null {
        return this.editor;
    }

    destroy(): void {
        this.editor?.destroy();
        this.editor = null;
        this.updateCallbacks = [];
    }
}
