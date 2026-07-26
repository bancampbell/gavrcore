import type { Selection } from '../values/Selection';

export interface EditorPort {
    getHTML(): string;
    setContent(content: string): void;
    insertContent(html: string, position?: number): void;
    getSelection(): Selection;
    setTextSelection(from: number, to?: number): void;
    getNodeAt(pos: number): { type: string; attrs: Record<string, any> } | null;
    updateNode(pos: number, attrs: Record<string, any>): void;
    isActive(): boolean;
    onUpdate(callback: (html: string) => void): () => void;
    destroy(): void;
}
