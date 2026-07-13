import { ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { ImageData } from '../types/editor';

export function useImageHandlers() {
    const selectedImageData = ref<ImageData | null>(null);
    const selectedImageAlign = ref<string>('');

    const getImageData = (img: HTMLImageElement): ImageData => {
        let align = '';
        const style = img.getAttribute('style') || '';
        if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
            align = 'center';
        } else if (style.includes('margin-left: 0')) {
            align = 'left';
        } else if (style.includes('margin-right: 0')) {
            align = 'right';
        }
        selectedImageAlign.value = align;

        let width = img.getAttribute('width') || '';
        let height = img.getAttribute('height') || '';
        if (!width && style) {
            const widthMatch = style.match(/width:\s*(\d+)px/);
            if (widthMatch) width = widthMatch[1];
        }
        if (!height && style) {
            const heightMatch = style.match(/height:\s*(\d+)px/);
            if (heightMatch) height = heightMatch[1];
        }

        return {
            url: img.src,
            alt: img.alt,
            title: img.title,
            width,
            height,
            align,
        };
    };

    const clearSelection = (): void => {
        document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
        selectedImageData.value = null;
        selectedImageAlign.value = '';
    };

    const handleImageClick = (e: MouseEvent): void => {
        const target = e.target as HTMLElement;
        const img = target.closest('img');

        if (img) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            selectedImageData.value = getImageData(img);
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
        } else {
            clearSelection();
        }
    };

    const setImageAlign = (editor: Editor, align: 'left' | 'center' | 'right') => {
        if (!editor) return;

        const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

        if (selectedImg) {
            selectedImageAlign.value = align;

            const { state } = editor;
            const { tr } = state;

            const width = selectedImg.getAttribute('width') || '';
            const height = selectedImg.getAttribute('height') || '';

            let style = 'display: block; ';
            if (width) style += `width: ${width}px; `;
            if (height) style += `height: ${height}px; `;

            if (align === 'left') style += 'margin-left: 0; margin-right: auto;';
            else if (align === 'center') style += 'margin-left: auto; margin-right: auto;';
            else if (align === 'right') style += 'margin-left: auto; margin-right: 0;';

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                    tr.setNodeMarkup(pos, undefined, {
                        ...node.attrs,
                        width: width || undefined,
                        height: height || undefined,
                        style: style,
                        align: align,
                    });
                }
            });

            editor.view.dispatch(tr);

            setTimeout(() => {
                const imgEl = document.querySelector(`.tiptap img[src="${selectedImg.src}"]`) as HTMLImageElement;
                if (imgEl) {
                    imgEl.style.cssText = style;
                }
            }, 50);

        } else {
            selectedImageAlign.value = '';
            editor.chain().focus().setTextAlign(align).run();
        }
    };

    const alignImageLeft = (editor: Editor) => setImageAlign(editor, 'left');
    const centerImage = (editor: Editor) => setImageAlign(editor, 'center');
    const alignImageRight = (editor: Editor) => setImageAlign(editor, 'right');

    const openImageModal = (emit: any) => {
        if (selectedImageData.value) {
            emit('openImageModal', selectedImageData.value);
        } else {
            emit('openImageModal');
        }
    };

    const normalizeUrl = (url: string) => {
        if (!url) return '';
        return url.replace(/^https?:\/\/[^\/]+/, '');
    };

    const updateImage = (editor: Editor, oldUrl: string, newData: ImageData) => {
        if (!editor) return;

        const { state } = editor;
        let found = false;
        const normalizedOldUrl = normalizeUrl(oldUrl);

        const width = newData.width || '';
        const height = newData.height || '';

        let style = 'display: block; ';
        if (width) style += `width: ${width}px; `;
        if (height) style += `height: ${height}px; `;

        if (newData.align === 'left') style += 'margin-left: 0; margin-right: auto;';
        else if (newData.align === 'center') style += 'margin-left: auto; margin-right: auto;';
        else if (newData.align === 'right') style += 'margin-left: auto; margin-right: 0;';

        state.doc.descendants((node, pos) => {
            if (node.type.name === 'image') {
                const nodeUrl = node.attrs.src || '';
                const normalizedNodeUrl = normalizeUrl(nodeUrl);

                if (normalizedNodeUrl === normalizedOldUrl || nodeUrl === oldUrl) {
                    const { tr } = state;

                    const newAttrs: any = {
                        src: newData.url,
                        alt: newData.alt || node.attrs.alt || '',
                        title: newData.title || node.attrs.title || '',
                        width: width || undefined,
                        height: height || undefined,
                        style: style,
                        align: newData.align || '',
                    };

                    tr.setNodeMarkup(pos, undefined, newAttrs);
                    editor.view.dispatch(tr);
                    found = true;
                    return false;
                }
            }
            return true;
        });

        if (!found) {
            const currentHtml = editor.getHTML();

            let newImgHtml = `<img src="${newData.url}"`;
            if (newData.alt && newData.alt !== '') newImgHtml += ` alt="${newData.alt}"`;
            if (newData.title && newData.title !== '') newImgHtml += ` title="${newData.title}"`;
            if (width) newImgHtml += ` width="${width}"`;
            if (height) newImgHtml += ` height="${height}"`;
            newImgHtml += ` style="${style}" />`;

            const escapedOldUrl = oldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const escapedNormalized = normalizedOldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            const patterns = [
                new RegExp(`<img[^>]*src=["']${escapedOldUrl}["'][^>]*>`, 'g'),
                new RegExp(`<img[^>]*src=["']${escapedNormalized}["'][^>]*>`, 'g'),
            ];

            let updatedHtml = currentHtml;
            let replaced = false;

            for (const pattern of patterns) {
                pattern.lastIndex = 0;
                if (pattern.test(currentHtml)) {
                    pattern.lastIndex = 0;
                    updatedHtml = currentHtml.replace(pattern, newImgHtml);
                    replaced = true;
                    break;
                }
            }

            if (replaced && updatedHtml !== currentHtml) {
                editor.commands.setContent(updatedHtml);
            }
        }
    };

    return {
        selectedImageData,
        selectedImageAlign,
        alignImageLeft,
        centerImage,
        alignImageRight,
        handleImageClick,
        openImageModal,
        updateImage,
        clearSelection,
    };
}
