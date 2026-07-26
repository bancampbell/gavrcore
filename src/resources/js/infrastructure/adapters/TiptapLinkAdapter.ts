import type { Editor } from '@tiptap/core';
import type { Node } from '@tiptap/pm/model';
import type { LinkPort } from '../../domain/ports/LinkPort';
import { LinkData } from '../../domain/values/LinkData';

export class TiptapLinkAdapter implements LinkPort {
    private editor: Editor | null = null;

    init(editor: Editor): void {
        this.editor = editor;
    }

    insertLink(data: LinkData, position?: { from: number; to: number }): void {
        if (!this.editor) return;

        const { from, to } = position ?? this.editor.state.selection;
        const text = from !== to ? this.editor.state.doc.textBetween(from, to) : data.text;
        const linkHtml = this.buildLinkHtml(data.url, text, data.target, data.title);

        this.editor.chain().focus().setTextSelection({ from, to }).insertContent(linkHtml).run();
    }

    updateLink(oldText: string, data: LinkData): void {
        if (!this.editor) return;

        const newText = data.text || oldText;
        const linkHtml = this.buildLinkHtml(data.url, newText, data.target, data.title);

        let found = false;

        this.editor.state.doc.descendants((node: Node, pos: number) => {
            if (found) return false;
            if (node.isText && node.text === oldText) {
                const hasLink = node.marks.some(mark => mark.type.name === 'link');
                if (hasLink) {
                    this.editor?.chain().focus().setTextSelection({ from: pos, to: pos + oldText.length }).insertContent(linkHtml).run();
                    found = true;
                    return false;
                }
            }
            return true;
        });

        if (!found) {
            this.insertLink(data);
        }
    }

    getLinkAt(pos: number): LinkData | null {
        if (!this.editor) return null;

        const node = this.editor.state.doc.nodeAt(pos);
        if (!node) return null;

        const linkMark = node.marks.find(mark => mark.type.name === 'link');
        if (!linkMark) return null;

        return LinkData.create({
            url: linkMark.attrs.href || '',
            text: node.text || '',
            target: linkMark.attrs.target || '_self',
            title: linkMark.attrs.title || '',
        });
    }

    private buildLinkHtml(url: string, text: string, target: string, title: string): string {
        const linkTarget = target === '_blank' ? '_blank' : '_self';
        const linkTitle = title || '';
        return `<a href="${url}" target="${linkTarget}" title="${linkTitle}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${text}</a>`;
    }
}
