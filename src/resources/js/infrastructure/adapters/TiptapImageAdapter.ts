import type { Editor } from '@tiptap/core';
import type { Node } from '@tiptap/pm/model';
import type { ImagePort } from '../../domain/ports/ImagePort';
import { ImageData } from '../../domain/values/ImageData';

export class TiptapImageAdapter implements ImagePort {
    private editor: Editor | null = null;

    init(editor: Editor): void {
        this.editor = editor;
    }

    insertImage(data: ImageData, position?: number): void {
        if (!this.editor) return;

        const attrs: Record<string, any> = {
            src: data.url,
            alt: data.alt,
            title: data.title,
        };

        if (data.width) attrs.width = String(data.width);
        if (data.height) attrs.height = String(data.height);
        if (data.styleProps.align) attrs.align = data.styleProps.align;

        const styleParts: string[] = [];
        if (data.width) styleParts.push(`width: ${data.width}px`);
        if (data.height) styleParts.push(`height: ${data.height}px`);
        if (data.styleProps.float === 'left') {
            styleParts.push('float: left');
            if (data.styleProps.margin) styleParts.push(`margin-right: ${data.styleProps.margin}px`);
        } else if (data.styleProps.float === 'right') {
            styleParts.push('float: right');
            if (data.styleProps.margin) styleParts.push(`margin-left: ${data.styleProps.margin}px`);
        }
        if (styleParts.length > 0) attrs.style = styleParts.join('; ');

        const node = this.editor.state.schema.nodes.image.create(attrs);
        const pos = position ?? this.editor.state.selection.from;
        const tr = this.editor.state.tr.insert(pos, node);
        this.editor.view.dispatch(tr);
    }

    updateImage(pos: number, data: ImageData): void {
        if (!this.editor) return;

        const node = this.editor.state.doc.nodeAt(pos);
        if (!node || node.type.name !== 'image') return;

        const attrs: Record<string, any> = {
            ...node.attrs,
            src: data.url,
            alt: data.alt,
            title: data.title,
        };

        if (data.width) attrs.width = String(data.width);
        if (data.height) attrs.height = String(data.height);
        if (data.styleProps.align) attrs.align = data.styleProps.align; else delete attrs.align;

        const styleParts: string[] = [];
        if (data.width) styleParts.push(`width: ${data.width}px`);
        if (data.height) styleParts.push(`height: ${data.height}px`);
        if (data.styleProps.float === 'left') {
            styleParts.push('float: left');
            if (data.styleProps.margin) styleParts.push(`margin-right: ${data.styleProps.margin}px`);
        } else if (data.styleProps.float === 'right') {
            styleParts.push('float: right');
            if (data.styleProps.margin) styleParts.push(`margin-left: ${data.styleProps.margin}px`);
        }
        attrs.style = styleParts.join('; ') || null;

        const tr = this.editor.state.tr.setNodeMarkup(pos, undefined, attrs);
        this.editor.view.dispatch(tr);
    }

    getImageAt(pos: number): ImageData | null {
        if (!this.editor) return null;

        const node = this.editor.state.doc.nodeAt(pos);
        if (!node || node.type.name !== 'image') return null;

        const style = node.attrs.style || '';
        const floatMatch = style.match(/float:\s*(left|right)/);
        const marginMatch = style.match(/margin-(right|left):\s*(\d+)px/);

        return ImageData.create({
            url: node.attrs.src || '',
            alt: node.attrs.alt || '',
            title: node.attrs.title || '',
            width: node.attrs.width || null,
            height: node.attrs.height || null,
            align: node.attrs.align || null,
            float: floatMatch ? floatMatch[1] as 'left' | 'right' : undefined,
            margin: marginMatch ? marginMatch[2] : undefined,
        });
    }

    findImagePosition(url: string): number {
        if (!this.editor) return -1;

        const normalizedUrl = this.normalizeUrl(url);
        let foundPos = -1;

        this.editor.state.doc.descendants((node: Node, pos: number) => {
            if (node.type.name === 'image') {
                const nodeUrl = node.attrs.src || '';
                const normalizedNodeUrl = this.normalizeUrl(nodeUrl);
                if (normalizedNodeUrl === normalizedUrl || nodeUrl === url) {
                    foundPos = pos;
                    return false;
                }
            }
            return true;
        });

        return foundPos;
    }

    private normalizeUrl(url: string): string {
        return url.replace(/^https?:\/\/[^\/]+/, '');
    }
}
