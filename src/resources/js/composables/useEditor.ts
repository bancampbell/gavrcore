import { ref, readonly, type Ref } from 'vue';
import { useEditorContext } from '../infrastructure/di/editorContext';
import type { ImageData } from '../domain/values/ImageData';
import type { LinkData } from '../domain/values/LinkData';

export function useEditor() {
    const context = useEditorContext();

    if (!context) {
        return {
            selectedImage: readonly(ref(null)) as Readonly<Ref<ImageData | null>>,
            selectedImagePos: readonly(ref(-1)) as Readonly<Ref<number>>,
            selectedLink: readonly(ref(null)) as Readonly<Ref<LinkData | null>>,
            isHtmlMode: ref(false),
            htmlContent: ref(''),
            getHTML: () => '',
            insertContent: () => {},
            getCursorPosition: () => 0,
            insertImage: () => {},
            updateImage: () => {},
            selectImage: () => {},
            selectImageAt: () => {},
            clearImageSelection: () => {},
            insertLink: () => {},
            updateLink: () => {},
            selectLink: () => {},
            clearLinkSelection: () => {},
            getCurrentHtml: () => '',
            applyHtml: () => {},
            deleteNode: () => {},
            editorAdapter: null as any,
            imageAdapter: null as any,
            linkAdapter: null as any,
        };
    }

    const {
        editorAdapter,
        imageAdapter,
        linkAdapter,
        insertImageUseCase,
        updateImageUseCase,
        insertLinkUseCase,
        updateLinkUseCase,
        deleteNodeUseCase,
        toggleHtmlModeUseCase,
    } = context;

    const selectedImage = ref<ImageData | null>(null);
    const selectedImagePos = ref<number>(-1);
    const selectedLink = ref<LinkData | null>(null);

    const isHtmlMode = ref(false);
    const htmlContent = ref('');

    function getHTML(): string {
        return editorAdapter.getHTML();
    }

    function insertContent(html: string, position?: number): void {
        editorAdapter.insertContent(html, position);
    }

    function getCursorPosition(): number {
        return editorAdapter.getSelection().from;
    }

    function insertImage(data: ImageData, position?: number): void {
        insertImageUseCase.execute(data, position);
    }

    function updateImage(oldUrl: string, data: ImageData): void {
        updateImageUseCase.execute(oldUrl, data);
    }

    function selectImage(url: string): void {
        const pos = imageAdapter.findImagePosition(url);
        if (pos === -1) {
            clearImageSelection();
            return;
        }
        const imageData = imageAdapter.getImageAt(pos);
        selectedImage.value = imageData;
        selectedImagePos.value = pos;
    }

    function selectImageAt(pos: number): void {
        const imageData = imageAdapter.getImageAt(pos);
        if (imageData) {
            selectedImage.value = imageData;
            selectedImagePos.value = pos;
        }
    }

    function clearImageSelection(): void {
        selectedImage.value = null;
        selectedImagePos.value = -1;
    }

    function insertLink(data: LinkData): void {
        insertLinkUseCase.execute(data);
    }

    function updateLink(oldText: string, data: LinkData): void {
        updateLinkUseCase.execute(oldText, data);
    }

    function selectLink(data: LinkData): void {
        selectedLink.value = data;
    }

    function clearLinkSelection(): void {
        selectedLink.value = null;
    }

    function getCurrentHtml(): string {
        return toggleHtmlModeUseCase.getCurrentHtml();
    }

    function applyHtml(html: string): void {
        toggleHtmlModeUseCase.applyHtml(html);
    }

    function deleteNode(pos: number): void {
        deleteNodeUseCase.execute(pos);
    }

    return {
        selectedImage: readonly(selectedImage) as Readonly<Ref<ImageData | null>>,
        selectedImagePos: readonly(selectedImagePos) as Readonly<Ref<number>>,
        selectedLink: readonly(selectedLink) as Readonly<Ref<LinkData | null>>,
        isHtmlMode,
        htmlContent,

        getHTML,
        insertContent,
        getCursorPosition,
        insertImage,
        updateImage,
        selectImage,
        selectImageAt,
        clearImageSelection,
        insertLink,
        updateLink,
        selectLink,
        clearLinkSelection,
        getCurrentHtml,
        applyHtml,
        deleteNode,

        editorAdapter,
        imageAdapter,
        linkAdapter,
    };
}
