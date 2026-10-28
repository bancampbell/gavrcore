import type { Editor } from '@tiptap/core';
import { getMarkRange } from '@tiptap/core';
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
        const range = this.findLinkRange(oldText);

        if (!range) {
            // Ссылку не нашли — документ не трогаем. Вставка новой ссылки
            // рядом существующую не удаляет и даёт «СтароеНовое» из двух якорей.
            console.warn(`TiptapLinkAdapter.updateLink: ссылка с текстом "${oldText}" не найдена`);
            return;
        }

        this.editor.chain().focus().setTextSelection(range).insertContent(linkHtml).run();
    }

    // Диапазон существующей ссылки: точное совпадение текста, а при рассинхроне
    // textContent(DOM) и text-узлов ProseMirror (якорь из нескольких узлов,
    // например с форматированием внутри) — разметка link под текущим выделением.
    private findLinkRange(oldText: string): { from: number; to: number } | null {
        const ed = this.editor;
        if (!ed) return null;

        const linkType = ed.schema.marks.link;
        if (!linkType) return null;

        let exact: { from: number; to: number } | null = null;

        ed.state.doc.descendants((node: Node, pos: number) => {
            if (exact) return false;
            if (!node.isText) return true;
            const hasLink = node.marks.some(mark => mark.type === linkType);
            if (hasLink && node.text === oldText) {
                exact = { from: pos, to: pos + node.nodeSize };
                return false;
            }
            return true;
        });

        if (exact) return exact;

        const { selection } = ed.state;
        const fromPos = selection.$from.pos;
        const candidates = [fromPos, fromPos - 1];

        for (const pos of candidates) {
            const range = this.getLinkMarkRange(pos);
            if (range) return range;
        }

        return null;
    }

    getLinkAt(pos: number): LinkData | null {
        if (!this.editor) return null;

        const $pos = this.editor.state.doc.resolve(pos);
        // внутри текстового узла nodeAfter пуст — берём ближайший соседний узел
        const node = $pos.nodeAfter ?? $pos.nodeBefore;
        if (!node) return null;

        const linkMark = node.marks.find(mark => mark.type.name === 'link');
        if (!linkMark) return null;

        // текст ВСЕГО якоря по полному mark-диапазону ссылки, а не текст
        // одного узла: якорь из нескольких узлов (форматирование внутри)
        // иначе отдаётся частично
        const range = this.getLinkMarkRange(pos) ?? this.getLinkMarkRange(pos - 1);
        const text = range
            ? this.editor.state.doc.textBetween(range.from, range.to)
            : (node.text ?? '');

        return LinkData.create({
            url: linkMark.attrs.href || '',
            text,
            target: linkMark.attrs.target || '_self',
            title: linkMark.attrs.title || '',
        });
    }

    private getLinkMarkRange(pos: number): { from: number; to: number } | null {
        const ed = this.editor;
        if (!ed) return null;
        const linkType = ed.schema.marks.link;
        if (!linkType || pos < 0 || pos > ed.state.doc.content.size) return null;
        const range = getMarkRange(ed.state.doc.resolve(pos), linkType);
        return range ? { from: range.from, to: range.to } : null;
    }

    private buildLinkHtml(url: string, text: string, target: string, title: string): string {
        const linkTarget = target === '_blank' ? '_blank' : '_self';
        const linkTitle = title || '';
        return `<a href="${this.escapeAttribute(url)}" target="${linkTarget}" title="${this.escapeAttribute(linkTitle)}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${this.escapeText(text)}</a>`;
    }

    private escapeAttribute(value: string): string {
        return value
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    private escapeText(value: string): string {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
}
