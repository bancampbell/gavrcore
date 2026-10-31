import type { Editor } from '@tiptap/core';
import { getMarkRange } from '@tiptap/core';
import type { LinkPort } from '../../domain/ports/LinkPort';
import { LinkData, type LinkPosition } from '../../domain/values/LinkData';

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

    // Редактирование строго по позиции: _pos — mark-диапазон ссылки, захваченный
    // при клике/синке из DOM (getMarkRange). Текстовый поиск по oldText удалён:
    // на плотно стоящих похожих якорях он цеплял соседнюю ссылку или промахивался,
    // а «не нашёл → вставить рядом» давало дубли «СтароеНовое» из двух якорей.
    // Нет позиции или позиция протухла — документ не трогаем, только warn.
    updateLink(oldText: string, data: LinkData): void {
        if (!this.editor) return;

        const range = this.resolveLinkRange(data._pos, oldText);
        if (!range) return;

        const newText = data.text || oldText;
        const linkHtml = this.buildLinkHtml(data.url, newText, data.target, data.title);

        this.editor.chain().focus().setTextSelection({ from: range.from, to: range.to }).insertContent(linkHtml).run();
    }

    private resolveLinkRange(pos: LinkPosition | null, oldText: string): LinkPosition | null {
        const ed = this.editor;
        if (!ed) return null;

        const linkType = ed.schema.marks.link;
        if (!linkType) return null;

        if (!pos) {
            console.warn(`TiptapLinkAdapter.updateLink: нет позиции (_pos) для ссылки "${oldText}" — документ не изменён`);
            return null;
        }

        const { from, to } = pos;
        if (from < 0 || from >= to || to > ed.state.doc.content.size) {
            console.warn(`TiptapLinkAdapter.updateLink: позиция (${from}..${to}) вне документа для ссылки "${oldText}" — документ не изменён`);
            return null;
        }

        // Позиция протухла (документ менялся между открытием диалога и submit)?
        // На диапазоне уже нет mark link — ничего не затираем.
        if (!ed.state.doc.rangeHasMark(from, to, linkType)) {
            console.warn(`TiptapLinkAdapter.updateLink: позиция (${from}..${to}) больше не указывает на ссылку "${oldText}" — документ не изменён`);
            return null;
        }

        return { from, to };
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
            _pos: range ?? null,
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
