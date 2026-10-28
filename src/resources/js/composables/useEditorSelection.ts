import { ref, computed, type Ref } from 'vue';
import { getMarkRange } from '@tiptap/core';
import type { TiptapEditorAdapter } from '@/infrastructure/adapters/TiptapEditorAdapter';
import type { TiptapImageAdapter } from '@/infrastructure/adapters/TiptapImageAdapter';
import { LinkData } from '@/domain/values/LinkData';
import type { ImageData as DomainImageData } from '@/domain/values/ImageData';

export interface SelectionState {
    selectedImageAlign: Ref<string>;
    selectedImageFloat: Ref<string>;
    selectedImageDataForToolbar: Readonly<Ref<{
        url: string;
        alt: string;
        title: string;
        width: string;
        height: string;
        align: string;
        float: string;
        margin: string;
    } | null>>;
    selectedLinkDataForToolbar: Readonly<Ref<{
        oldText: string;
        url: string;
        text: string;
        target: string;
        title: string;
    } | null>>;
}

export function useEditorSelection(
    editorAdapter: TiptapEditorAdapter,
    imageAdapter: TiptapImageAdapter,
    selectedImage: Ref<DomainImageData | null>,
    selectedLink: Ref<LinkData | null>,
    clearImageSelection: () => void,
    clearLinkSelection: () => void,
    selectLink: (data: LinkData) => void,
    selectImageAt: (pos: number) => void,
): SelectionState & {
    handleClick: (e: MouseEvent) => void;
    resetSelection: () => void;
    syncImageSelectionFromDOM: () => void;
} {
    const selectedImageAlign = ref('');
    const selectedImageFloat = ref('');

    const selectedImageDataForToolbar = computed(() => {
        const data = selectedImage.value;
        if (!data) return null;
        return {
            url: data.url,
            alt: data.alt,
            title: data.title,
            width: data.width ? String(data.width) : '',
            height: data.height ? String(data.height) : '',
            align: data.styleProps.align || '',
            float: data.styleProps.float || '',
            margin: data.styleProps.margin ? String(data.styleProps.margin) : '',
        };
    });

    const selectedLinkDataForToolbar = computed(() => {
        const data = selectedLink.value;
        if (!data) return null;
        return {
            oldText: data.oldText || data.text,
            url: data.url,
            text: data.text,
            target: data.target,
            title: data.title,
        };
    });

    function resetSelection(): void {
        document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
        document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
        clearImageSelection();
        clearLinkSelection();
        selectedImageAlign.value = '';
        selectedImageFloat.value = '';
    }

    function syncImageSelectionFromDOM(): void {
        const img = document.querySelector('.tiptap img.selected-image') as HTMLImageElement | null;
        if (!img) {
            resetSelection();
            return;
        }

        const imageId = img.getAttribute('data-image-id');
        if (imageId) {
            const pos = imageAdapter.findImageById(imageId);
            if (pos !== -1) {
                selectImageAt(pos);
            }
        }

        const wr = img.closest('.resize-wrapper');
        selectedImageAlign.value = wr?.getAttribute('data-align') || '';
        selectedImageFloat.value = wr?.getAttribute('data-float') || '';
    }

    // Повторное чтение выделенной ссылки из DOM: selectedLink в состоянии может
    // быть устаревшим (ссылку уже обновили, а класс .selected-link висит на той
    // же ссылке), поэтому перед открытием диалога берём актуальные атрибуты.
    function syncLinkSelectionFromDOM(): void {
        const link = document.querySelector('.tiptap a.selected-link') as HTMLAnchorElement | null;
        if (!link) {
            clearLinkSelection();
            return;
        }

        const linkText = fullLinkText(link);
        selectLink(LinkData.create({
            url: link.getAttribute('href') || '',
            text: linkText,
            target: link.getAttribute('target') || '_self',
            title: link.getAttribute('title') || '',
            oldText: linkText,
        }));
    }

    // Текст ссылки целиком: textBetween по полному mark-диапазону link
    // (getMarkRange по позиции якоря, расширение в обе стороны), а не
    // textContent кликнутого элемента. Якорь из нескольких DOM-элементов/
    // узлов (форматирование внутри, разъехавшаяся разметка из БД) иначе
    // отдаётся частично — одним словом.
    function fullLinkText(link: HTMLAnchorElement): string {
        const ed = editorAdapter.getEditor();
        if (!ed) return link.textContent ?? '';

        try {
            const pos = ed.view.posAtDOM(link, 0);
            const linkType = ed.schema.marks.link;
            const range = linkType
                ? getMarkRange(ed.state.doc.resolve(pos), linkType)
                : null;
            if (range) return ed.state.doc.textBetween(range.from, range.to);
        } catch {
            // элемент не из документа редактора — остаёмся на textContent
        }

        return link.textContent ?? '';
    }

    function handleClick(e: MouseEvent): void {
        const target = e.target as HTMLElement;

        if (
            target.closest('.modal-overlay') ||
            target.closest('.modal-content') ||
            target.closest('.toolbar') ||
            target.closest('.btn-toolbar')
        ) {
            return;
        }

        const link = target.closest('a');
        const img = target.closest('img');
        const isInsideTiptap = target.closest('.tiptap');

        if (!isInsideTiptap) {
            resetSelection();
            return;
        }

        if (link) {
            e.preventDefault();
            // текст якоря целиком по mark-диапазону (fullLinkText), а не
            // textContent кликнутого элемента: иначе якорь из нескольких
            // узлов открывается в диалоге одним кликнутым словом.
            const linkText = fullLinkText(link);
            const linkData = LinkData.create({
                url: link.getAttribute('href') || '',
                text: linkText,
                target: link.getAttribute('target') || '_self',
                title: link.getAttribute('title') || '',
                oldText: linkText,
            });
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            link.classList.add('selected-link');
            selectLink(linkData);
            clearImageSelection();
            selectedImageAlign.value = '';
            selectedImageFloat.value = '';
            return;
        }

        if (img) {
            e.preventDefault();
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
            clearLinkSelection();

            const imageId = img.getAttribute('data-image-id');
            if (imageId) {
                const pos = imageAdapter.findImageById(imageId);
                if (pos !== -1) {
                    selectImageAt(pos);
                }
            }

            const wr = img.closest('.resize-wrapper');
            selectedImageAlign.value = wr?.getAttribute('data-align') || '';
            selectedImageFloat.value = wr?.getAttribute('data-float') || '';
            return;
        }

        resetSelection();
    }

    return {
        selectedImageAlign,
        selectedImageFloat,
        selectedImageDataForToolbar,
        selectedLinkDataForToolbar,
        handleClick,
        resetSelection,
        syncImageSelectionFromDOM,
        syncLinkSelectionFromDOM,
    };
}
