import { ref, computed, type Ref } from 'vue';
import { getMarkRange } from '@tiptap/core';
import type { TiptapEditorAdapter } from '@/infrastructure/adapters/TiptapEditorAdapter';
import type { TiptapImageAdapter } from '@/infrastructure/adapters/TiptapImageAdapter';
import type { TiptapLinkAdapter } from '@/infrastructure/adapters/TiptapLinkAdapter';
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
    linkAdapter: TiptapLinkAdapter,
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
    syncLinkSelectionFromDOM: () => void;
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

    // Повторное чтение выделенной ссылки перед открытием диалога.
    // Два случая:
    // 1) Класс .selected-link на месте — читаем свежие атрибуты и mark-диапазон
    //    прямо с элемента (selectedLink мог устареть: ссылку обновили, а класс
    //    висит на той же ссылке).
    // 2) Класса нет — его стёр перерендер DOM редактора (клик двигает каретку
    //    ProseMirror, и параграф пересобирается). Состояние из клика при этом
    //    НОВЕЕ и точнее DOM-класса: обновляем его по сохранённой позиции (_pos),
    //    а не сбрасываем — иначе идентификация снова свалится в каретку и при
    //    плотно стоящих похожих якорях цепнет соседнюю ссылку.
    // Сброс только когда читать нечего: ни класса, ни позиции.
    function syncLinkSelectionFromDOM(): void {
        const link = document.querySelector('.tiptap a.selected-link') as HTMLAnchorElement | null;
        if (link) {
            const linkText = fullLinkText(link);
            selectLink(LinkData.create({
                url: link.getAttribute('href') || '',
                text: linkText,
                target: link.getAttribute('target') || '_self',
                title: link.getAttribute('title') || '',
                oldText: linkText,
                _pos: linkRangeFromDOM(link),
            }));
            return;
        }

        const current = selectedLink.value;
        const fresh = current?._pos ? linkAdapter.getLinkAt(current._pos.from) : null;
        if (fresh) {
            selectLink(LinkData.create({
                url: fresh.url,
                text: fresh.text,
                target: fresh.target,
                title: fresh.title,
                oldText: fresh.text,
                _pos: fresh._pos ?? current?._pos ?? null,
            }));
            return;
        }

        clearLinkSelection();
    }

    // Mark-диапазон ссылки по её DOM-элементу: posAtDOM по началу якоря +
    // getMarkRange (расширение в обе стороны до границ mark link). Это _pos,
    // по которому updateLink правит узел позиционно, без текстового поиска.
    function linkRangeFromDOM(link: HTMLAnchorElement): { from: number; to: number } | null {
        const ed = editorAdapter.getEditor();
        if (!ed) return null;

        try {
            const pos = ed.view.posAtDOM(link, 0);
            const linkType = ed.schema.marks.link;
            return linkType
                ? getMarkRange(ed.state.doc.resolve(pos), linkType)
                : null;
        } catch {
            // элемент не из документа редактора — позиции нет
            return null;
        }
    }

    // Текст ссылки целиком: textBetween по полному mark-диапазону link
    // (getMarkRange по позиции якоря, расширение в обе стороны), а не
    // textContent кликнутого элемента. Якорь из нескольких DOM-элементов/
    // узлов (форматирование внутри, разъехавшаяся разметка из БД) иначе
    // отдаётся частично — одним словом.
    function fullLinkText(link: HTMLAnchorElement): string {
        const ed = editorAdapter.getEditor();
        if (!ed) return link.textContent ?? '';

        const range = linkRangeFromDOM(link);
        if (range) return ed.state.doc.textBetween(range.from, range.to);

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
            // узлов открывается в диалоге одним кликнутым словом. Вместе
            // с текстом захватываем и сам mark-диапазон (_pos) — по нему
            // updateLink правит именно эту ссылку, а не первую с таким текстом.
            const linkText = fullLinkText(link);
            const linkData = LinkData.create({
                url: link.getAttribute('href') || '',
                text: linkText,
                target: link.getAttribute('target') || '_self',
                title: link.getAttribute('title') || '',
                oldText: linkText,
                _pos: linkRangeFromDOM(link),
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
