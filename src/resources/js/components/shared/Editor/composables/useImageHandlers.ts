import { ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { Node } from '@tiptap/pm/model';
import type { ImageData } from '../types/editor';

export function useImageHandlers() {
    const selectedImageData = ref<ImageData | null>(null);
    const selectedImageAlign = ref<string>('');
    const selectedImageElement = ref<HTMLImageElement | null>(null);
    const selectedImagePos = ref<number>(-1);
    const currentFloat = ref<string>('');
    const currentMargin = ref<string>('');

    const getImageData = (img: HTMLImageElement): ImageData => {
        const style = img.getAttribute('style') || '';

        let align = '';
        let float = '';
        let margin = '';

        if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
            align = 'center';
        } else if (style.includes('margin-left: 0') && !style.includes('margin-left: auto')) {
            align = 'left';
        } else if (style.includes('margin-right: 0') && !style.includes('margin-right: auto')) {
            align = 'right';
        }

        const floatMatch = style.match(/float:\s*(left|right)/);
        if (floatMatch) float = floatMatch[1];

        const marginMatch = style.match(/margin(?:-right|-left)?:\s*(\d+)px/);
        if (marginMatch) margin = marginMatch[1];

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

        currentFloat.value = float;
        currentMargin.value = margin;

        return {
            url: img.src,
            alt: img.alt,
            title: img.title,
            width,
            height,
            align,
            float,
            margin,
        };
    };

    const clearSelection = (): void => {
        document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
        selectedImageData.value = null;
        selectedImageAlign.value = '';
        selectedImageElement.value = null;
        selectedImagePos.value = -1;
    };

    const handleImageClick = (e: MouseEvent): void => {
        const target = e.target as HTMLElement;
        const img = target.closest('img');

        if (img && img.closest('.tiptap')) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            selectedImageElement.value = img;
            selectedImageData.value = getImageData(img);

            const editor = (window as any).__editor;
            if (editor) {
                const { state } = editor;
                let pos = -1;
                state.doc.descendants((node: Node, nodePos: number) => {
                    if (node.type.name === 'image' && node.attrs.src === img.src) {
                        pos = nodePos;
                        return false;
                    }
                    return true;
                });
                selectedImagePos.value = pos;
            }

            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
        } else {
            clearSelection();
        }
    };

    const setImageAlign = (editor: Editor, align: 'left' | 'center' | 'right') => {
        if (!editor) return;

        const selectedImg = selectedImageElement.value;
        const savedPos = selectedImagePos.value;

        if (!selectedImg || savedPos === -1) {
            editor.chain().focus().setTextAlign(align).run();
            return;
        }

        const { state, view } = editor;
        const { tr } = state;

        const width = selectedImg.getAttribute('width') || '';
        const height = selectedImg.getAttribute('height') || '';

        let style = '';
        if (width) style += `width: ${width}px; `;
        if (height) style += `height: ${height}px; `;
        style += 'display: block; ';

        if (align === 'left') style += 'margin-left: 0; margin-right: auto; ';
        else if (align === 'center') style += 'margin-left: auto; margin-right: auto; ';
        else if (align === 'right') style += 'margin-left: auto; margin-right: 0; ';

        if (currentFloat.value === 'left') {
            style += 'float: left; ';
            if (currentMargin.value) style += `margin-right: ${currentMargin.value}px; `;
        } else if (currentFloat.value === 'right') {
            style += 'float: right; ';
            if (currentMargin.value) style += `margin-left: ${currentMargin.value}px; `;
        }

        const node = state.doc.nodeAt(savedPos);
        if (node && node.type.name === 'image') {
            const newAttrs = {
                ...node.attrs,
                style: style.trim(),
            };
            const transaction = tr.setNodeMarkup(savedPos, undefined, newAttrs);
            view.dispatch(transaction);

            setTimeout(() => {
                if (selectedImg) {
                    selectedImg.style.cssText = style.trim();
                    selectedImageData.value = getImageData(selectedImg);
                }
            }, 50);
        }
    };

    const alignImageLeft = (editor: Editor) => setImageAlign(editor, 'left');
    const centerImage = (editor: Editor) => setImageAlign(editor, 'center');
    const alignImageRight = (editor: Editor) => setImageAlign(editor, 'right');

    const openImageModal = (emit: any) => {
        if (selectedImageData.value && selectedImagePos.value === -1) {
            const editor = (window as any).__editor;
            if (editor) {
                const { state } = editor;
                const imgUrl = selectedImageData.value.url;
                const normalizedUrl = imgUrl.replace(/^https?:\/\/[^\/]+/, '');

                state.doc.descendants((node: Node, pos: number) => {
                    if (node.type.name === 'image') {
                        const nodeUrl = node.attrs.src || '';
                        const normalizedNodeUrl = nodeUrl.replace(/^https?:\/\/[^\/]+/, '');
                        if (normalizedNodeUrl === normalizedUrl || nodeUrl === imgUrl) {
                            selectedImagePos.value = pos;
                            return false;
                        }
                    }
                    return true;
                });
            }
        }

        if (selectedImageData.value) {
            emit('openImageModal', {
                ...selectedImageData.value,
                _pos: selectedImagePos.value
            });
        } else {
            emit('openImageModal');
        }
    };

    const normalizeUrl = (url: string) => {
        if (!url) return '';
        return url.replace(/^https?:\/\/[^\/]+/, '');
    };

    const buildImageStyle = (data: {
        width?: string;
        height?: string;
        align?: string;
        float?: string;
        margin?: string;
    }) => {
        let style = '';

        if (data.width && data.width !== '') {
            style += `width: ${data.width}px; `;
        }
        if (data.height && data.height !== '') {
            style += `height: ${data.height}px; `;
        }

        style += 'display: block; ';

        if (data.align === 'left') {
            style += 'margin-left: 0; margin-right: auto; ';
        } else if (data.align === 'center') {
            style += 'margin-left: auto; margin-right: auto; ';
        } else if (data.align === 'right') {
            style += 'margin-left: auto; margin-right: 0; ';
        }

        if (data.float === 'left') {
            style += 'float: left; ';
            if (data.margin && data.margin !== '') {
                style += `margin-right: ${data.margin}px; `;
            }
        } else if (data.float === 'right') {
            style += 'float: right; ';
            if (data.margin && data.margin !== '') {
                style += `margin-left: ${data.margin}px; `;
            }
        }

        return style.trim();
    };

    const updateImage = (editor: Editor, oldUrl: string, newData: ImageData) => {
        const globalPos = (window as any).__selectedImagePos;
        if (globalPos !== undefined && globalPos !== -1) {
            selectedImagePos.value = globalPos;
            delete (window as any).__selectedImagePos;
        }

        if (!editor) return;

        const { state, view } = editor;
        const savedPos = selectedImagePos.value;
        const selectedImg = selectedImageElement.value;

        const float = newData.float || currentFloat.value || '';
        const margin = newData.margin || currentMargin.value || '';
        const align = newData.align || '';

        currentFloat.value = float;
        currentMargin.value = margin;

        if (savedPos !== -1) {
            const node = state.doc.nodeAt(savedPos);
            if (node && node.type.name === 'image') {
                const style = buildImageStyle({
                    width: newData.width || node.attrs.width,
                    height: newData.height || node.attrs.height,
                    align: align,
                    float: float,
                    margin: margin,
                });

                const newAttrs = {
                    ...node.attrs,
                    style: style,
                };

                const tr = state.tr.setNodeMarkup(savedPos, undefined, newAttrs);
                view.dispatch(tr);

                setTimeout(() => {
                    if (selectedImg) {
                        selectedImg.style.cssText = style;
                        selectedImageData.value = getImageData(selectedImg);
                    }
                }, 50);
                return;
            }
        }

        const normalizedOldUrl = normalizeUrl(oldUrl);
        let found = false;

        state.doc.descendants((node: Node, pos: number) => {
            if (found) return false;
            if (node.type.name === 'image') {
                const nodeUrl = node.attrs.src || '';
                const normalizedNodeUrl = normalizeUrl(nodeUrl);

                if (normalizedNodeUrl === normalizedOldUrl || nodeUrl === oldUrl) {
                    const style = buildImageStyle({
                        width: newData.width || node.attrs.width,
                        height: newData.height || node.attrs.height,
                        align: align,
                        float: float,
                        margin: margin,
                    });

                    const newAttrs = {
                        ...node.attrs,
                        style: style,
                    };

                    const tr = state.tr.setNodeMarkup(pos, undefined, newAttrs);
                    view.dispatch(tr);
                    found = true;
                    return false;
                }
            }
            return true;
        });
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
