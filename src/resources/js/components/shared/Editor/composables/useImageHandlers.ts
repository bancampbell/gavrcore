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

            const marginStyles: Record<string, string> = {
                left: 'margin-left: 0; margin-right: auto;',
                center: 'margin-left: auto; margin-right: auto;',
                right: 'margin-left: auto; margin-right: 0;',
            };

            const style = `display: block; ${marginStyles[align]}`;

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                    tr.setNodeMarkup(pos, undefined, {
                        ...node.attrs,
                        style: style,
                        align: align,
                    });
                }
            });

            editor.view.dispatch(tr);
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

    const updateImage = (editor: Editor, oldUrl: string, newData: ImageData) => {
        if (!editor) return;

        const { state } = editor;
        let found = false;

        // Нормализуем URL для поиска
        const normalizeUrl = (url: string) => {
            let normalized = url;
            // Убираем http:// или https:// и домен
            normalized = normalized.replace(/^https?:\/\/[^\/]+/, '');
            return normalized;
        };

        const normalizedOldUrl = normalizeUrl(oldUrl);

        state.doc.descendants((node, pos) => {
            if (node.type.name === 'image') {
                const nodeUrl = node.attrs.src || '';
                const normalizedNodeUrl = normalizeUrl(nodeUrl);

                // Сравниваем нормализованные URL или оригинальные
                if (normalizedNodeUrl === normalizedOldUrl || nodeUrl === oldUrl) {
                    const newAttrs = {
                        ...node.attrs,
                        src: newData.url,
                        alt: newData.alt || node.attrs.alt,
                        title: newData.title || node.attrs.title,
                        width: newData.width || node.attrs.width,
                        height: newData.height || node.attrs.height,
                    };

                    const { tr } = state;
                    tr.setNodeMarkup(pos, undefined, newAttrs);
                    editor.view.dispatch(tr);
                    found = true;
                    return false;
                }
            }
            return true;
        });

        if (!found) {
            // Fallback: ищем по src в HTML
            const currentHtml = editor.getHTML();
            const escapedOldUrl = oldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const escapedNormalizedOldUrl = normalizedOldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

            // Пробуем оба варианта
            const patterns = [
                new RegExp(`<img[^>]*src=["']${escapedOldUrl}["'][^>]*>`, 'g'),
                new RegExp(`<img[^>]*src=["']${escapedNormalizedOldUrl}["'][^>]*>`, 'g'),
            ];

            let updatedHtml = currentHtml;
            let replaced = false;

            for (const pattern of patterns) {
                if (pattern.test(currentHtml)) {
                    // Сбрасываем lastIndex после test
                    pattern.lastIndex = 0;
                    const newImgHtml = `<img src="${newData.url}" alt="${newData.alt || ''}" title="${newData.title || ''}" />`;
                    updatedHtml = currentHtml.replace(pattern, newImgHtml);
                    replaced = true;
                    break;
                }
            }

            if (replaced) {
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
