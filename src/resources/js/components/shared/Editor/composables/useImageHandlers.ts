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

    const alignImageLeft = (editor: Editor) => {
        if (!editor) return;

        const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

        if (selectedImg) {
            selectedImageAlign.value = 'left';
            const container = selectedImg.parentElement;
            if (container) {
                container.style.display = 'block';
                container.style.textAlign = 'left';
            }

            selectedImg.style.display = 'block';
            selectedImg.style.marginLeft = '0';
            selectedImg.style.marginRight = 'auto';

            const { state } = editor;
            const { tr } = state;

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                    tr.setNodeMarkup(pos, undefined, {
                        ...node.attrs,
                        style: `display: block; margin-left: 0; margin-right: auto; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                    });
                }
            });

            editor.view.dispatch(tr);
        } else {
            selectedImageAlign.value = '';
            editor.chain().focus().setTextAlign('left').run();
        }
    };

    const centerImage = (editor: Editor) => {
        if (!editor) return;

        const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

        if (selectedImg) {
            selectedImageAlign.value = 'center';
            const container = selectedImg.parentElement;
            if (container) {
                container.style.display = 'block';
                container.style.textAlign = 'center';
            }

            selectedImg.style.display = 'block';
            selectedImg.style.marginLeft = 'auto';
            selectedImg.style.marginRight = 'auto';

            const { state } = editor;
            const { tr } = state;

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                    tr.setNodeMarkup(pos, undefined, {
                        ...node.attrs,
                        style: `display: block; margin-left: auto; margin-right: auto; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                    });
                }
            });

            editor.view.dispatch(tr);
        } else {
            selectedImageAlign.value = '';
            editor.chain().focus().setTextAlign('center').run();
        }
    };

    const alignImageRight = (editor: Editor) => {
        if (!editor) return;

        const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

        if (selectedImg) {
            selectedImageAlign.value = 'right';
            const container = selectedImg.parentElement;
            if (container) {
                container.style.display = 'block';
                container.style.textAlign = 'right';
            }

            selectedImg.style.display = 'block';
            selectedImg.style.marginLeft = 'auto';
            selectedImg.style.marginRight = '0';

            const { state } = editor;
            const { tr } = state;

            state.doc.descendants((node, pos) => {
                if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                    tr.setNodeMarkup(pos, undefined, {
                        ...node.attrs,
                        style: `display: block; margin-left: auto; margin-right: 0; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                    });
                }
            });

            editor.view.dispatch(tr);
        } else {
            selectedImageAlign.value = '';
            editor.chain().focus().setTextAlign('right').run();
        }
    };

    const handleImageClick = (e: MouseEvent) => {
        const target = e.target as HTMLElement;
        const img = target.closest('img');
        if (img) {
            e.preventDefault();
            e.stopPropagation();
            selectedImageData.value = getImageData(img);
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
            if ((window as any).__selectedLinkData) {
                document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
                (window as any).__selectedLinkData = null;
            }
        } else {
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            selectedImageData.value = null;
            selectedImageAlign.value = '';
        }
    };

    const openImageModal = (emit: any) => {
        if (selectedImageData.value) {
            emit('openImageManager', selectedImageData.value);
        } else {
            emit('openImageManager');
        }
    };

    const updateImage = (editor: Editor, oldUrl: string, newData: ImageData) => {
        if (!editor) return;

        let style = '';
        if (newData.width && newData.width !== '') style += `width: ${newData.width}px; `;
        if (newData.height && newData.height !== '') style += `height: ${newData.height}px; `;

        const styleAttr = style ? ` style="${style.trim()}"` : '';
        const newImgHtml = `<img src="${newData.url}" alt="${newData.alt}" title="${newData.title}"${styleAttr} />`;

        const currentHtml = editor.getHTML();
        const oldImgRegex = new RegExp(`<img[^>]*src="${oldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"[^>]*>`, 'g');
        const updatedHtml = currentHtml.replace(oldImgRegex, newImgHtml);
        editor.commands.setContent(updatedHtml);
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
    };
}
