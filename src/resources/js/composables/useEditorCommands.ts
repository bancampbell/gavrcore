import type { Ref } from 'vue';
import type { TiptapEditorAdapter } from '@/infrastructure/adapters/TiptapEditorAdapter';
import { ImageData } from '@/domain/values/ImageData';
import { LinkData } from '@/domain/values/LinkData';

export function useEditorCommands(
    editorAdapter: TiptapEditorAdapter,
    selectedImageAlign: Ref<string>,
    selectedImageFloat: Ref<string>,
    resetSelection: () => void,
    insertImage: (data: ImageData, position?: number) => void,
    emit: (event: string, ...args: any[]) => void,
) {
    function alignImage(align: 'left' | 'center' | 'right'): void {
        const ed = editorAdapter.getEditor();
        if (!ed) return;

        const hasSelectedImage = document.querySelector('.tiptap img.selected-image');
        if (hasSelectedImage) {
            ed.commands.updateAttributes('image', { align, float: null });
            selectedImageAlign.value = align;
            selectedImageFloat.value = '';
        }

        emit('update:modelValue', editorAdapter.getHTML());
    }

    function floatImage(float: 'left' | 'right'): void {
        const ed = editorAdapter.getEditor();
        if (!ed) return;

        const hasSelectedImage = document.querySelector('.tiptap img.selected-image');
        if (hasSelectedImage) {
            ed.commands.updateAttributes('image', { float, align: null });
            selectedImageFloat.value = float;
            selectedImageAlign.value = '';
        }

        emit('update:modelValue', editorAdapter.getHTML());
    }

    function alignText(align: 'left' | 'center' | 'right'): void {
        const ed = editorAdapter.getEditor();
        if (!ed) return;

        resetSelection();

        ed.chain().focus().setTextAlign(align).run();
        emit('update:modelValue', editorAdapter.getHTML());
    }

    function handleOpenLinkModal(selectedLink: Ref<LinkData | null>): void {
        const linkData = selectedLink.value;
        if (linkData) {
            emit('editLink', {
                oldText: linkData.oldText || linkData.text,
                url: linkData.url,
                text: linkData.text,
                target: linkData.target,
                title: linkData.title,
            });
        } else {
            const selection = editorAdapter.getSelection();
            emit('openLinkModal', selection.isEmpty ? '' : selection.text);
        }
    }

    function handleImageInsert(data: any): void {
        const { url, alt, title, width, height, align, float, margin, _pos } = data;

        if (_pos !== undefined && _pos !== -1) {
            const ed = editorAdapter.getEditor();
            if (ed) {
                const node = ed.state.doc.nodeAt(_pos);
                if (node && node.type.name === 'image') {
                    const styleParts: string[] = [];
                    if (width) styleParts.push(`width: ${width}px`);
                    if (height) styleParts.push(`height: ${height}px`);
                    if (float === 'left') {
                        styleParts.push('float: left');
                        if (margin) styleParts.push(`margin-right: ${margin}px`);
                    } else if (float === 'right') {
                        styleParts.push('float: right');
                        if (margin) styleParts.push(`margin-left: ${margin}px`);
                    }

                    const attrs: Record<string, any> = {
                        ...node.attrs,
                        src: url,
                        alt: alt || '',
                        title: title || '',
                        align: align || null,
                        float: float || null,
                        style: styleParts.join('; ') || null,
                    };
                    if (width) attrs.width = String(width); else delete attrs.width;
                    if (height) attrs.height = String(height); else delete attrs.height;

                    const tr = ed.state.tr.setNodeMarkup(_pos, undefined, attrs);
                    ed.view.dispatch(tr);
                }
            }
        } else {
            const imageData = ImageData.create({
                url,
                alt: alt || '',
                title: title || '',
                width: width || null,
                height: height || null,
                align,
                float,
                margin,
            });
            insertImage(imageData, _pos ?? undefined);
        }

        emit('update:modelValue', editorAdapter.getHTML());
    }

    return {
        alignImage,
        floatImage,
        alignText,
        handleOpenLinkModal,
        handleImageInsert,
    };
}
