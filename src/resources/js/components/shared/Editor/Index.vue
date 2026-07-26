<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
        <RawHtmlEditor
            v-if="editorState.isHtmlMode.value"
            :model-value="editorState.htmlContent.value"
            @update:model-value="editorState.htmlContent.value = $event"
            @apply="handleApplyHtml"
            @close="editorState.isHtmlMode.value = false"
        />

        <div v-show="!editorState.isHtmlMode.value" class="flex-1 overflow-auto">
            <Toolbar
                :editor="editorAdapter.getEditor()"
                :selected-link-data="selectedLinkDataForToolbar"
                :selected-image-data="selectedImageDataForToolbar"
                :selected-image-align="selectedImageAlign"
                :selected-image-float="selectedImageFloat"
                :align-image-left="() => alignImage('left')"
                :center-image="() => alignImage('center')"
                :align-image-right="() => alignImage('right')"
                :float-image-left="() => floatImage('left')"
                :float-image-right="() => floatImage('right')"
                :align-text-left="() => alignText('left')"
                :align-text-center="() => alignText('center')"
                :align-text-right="() => alignText('right')"
                :open-link-modal="handleOpenLinkModal"
                :open-image-modal="handleOpenImageModal"
                :open-gallery-modal="handleOpenGalleryModal"
                :toggle-html="toggleHtmlMode"
                :open-file-manager="openFileManager"
                :open-form-modal="openFormModal"
                :is-raw-html-mode="editorState.isHtmlMode.value"
                @toggle-raw-html="emit('toggleRawHtml')"
            />

            <div class="tiptap p-4 h-full max-w-none" ref="editorElement"></div>
        </div>

        <GallerySelectModal
            :show="showGalleryModal"
            @close="showGalleryModal = false"
            @select="insertGallery"
        />

        <FormSelectModal
            :show="showFormModal"
            @close="showFormModal = false"
            @select="insertForm"
        />

        <ImageModal
            :key="imageModalKey"
            :show="showImageModal"
            :edit-data="imageEditData"
            @close="closeImageModal"
            @insert="handleImageInsert"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, nextTick, computed } from 'vue';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import TextAlign from '@tiptap/extension-text-align';
import Toolbar from './Toolbar.vue';
import { ResizableImage } from './extensions';
import { CustomDiv } from './extensions/CustomDiv';
import { PreserveAttributes } from './extensions/PreserveAttributes';
import { CleanPaste } from './extensions/CleanPaste';
import type { EditorProps, EditorEmits } from './types/editor';
import GallerySelectModal from '@/components/shared/GallerySelectModal.vue';
import FormSelectModal from '@/components/shared/FormSelectModal.vue';
import ImageModal from '@/Pages/Admin/Materials/components/ImageModal.vue';
import RawHtmlEditor from './RawHtmlEditor.vue';
import DOMPurify from 'dompurify';
import { useEditor } from '@/composables/useEditor';
import { ImageData } from '@/domain/values/ImageData';
import { LinkData } from '@/domain/values/LinkData';

const props = defineProps<EditorProps>();
const emit = defineEmits<EditorEmits>();

const editorState = useEditor();
const { editorAdapter, imageAdapter, linkAdapter } = editorState;

const editorElement = ref<HTMLElement>();
let editor: Editor | null = null;

const showGalleryModal = ref(false);
const showFormModal = ref(false);
const showImageModal = ref(false);
const imageEditData = ref<any>(null);
const imageModalKey = ref(0);
let contentUpdateTimeout: ReturnType<typeof setTimeout> | null = null;

const selectedImageAlign = ref('');
const selectedImageFloat = ref('');

const selectedImageDataForToolbar = computed(() => {
    const data = editorState.selectedImage.value;
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
    const data = editorState.selectedLink.value;
    if (!data) return null;
    return {
        oldText: data.oldText || data.text,
        url: data.url,
        text: data.text,
        target: data.target,
        title: data.title,
    };
});

function toggleHtmlMode(): void {
    if (!editorState.isHtmlMode.value) {
        editorState.htmlContent.value = editorState.getCurrentHtml();
        editorState.isHtmlMode.value = true;
    } else {
        handleApplyHtml();
    }
}

function handleApplyHtml(): void {
    editorState.applyHtml(editorState.htmlContent.value);
    editorState.isHtmlMode.value = false;
}

function handleOpenGalleryModal(): void {
    showGalleryModal.value = true;
}

function insertGallery(gallery: any): void {
    const shortcode = `[gallery id="${gallery.id}" name="${gallery.name || gallery.title}"]`;
    editorAdapter.insertContent(shortcode);
    showGalleryModal.value = false;
    emit('update:modelValue', editorAdapter.getHTML());
}

function openFormModal(): void {
    showFormModal.value = true;
}

function insertForm(form: any): void {
    const shortcode = `[form id="${form.id}"]`;
    editorAdapter.insertContent(shortcode);
    showFormModal.value = false;
    emit('update:modelValue', editorAdapter.getHTML());
}

function insertFormShortcode(formId: number): void {
    const selection = editorAdapter.getSelection();
    if (!selection.isEmpty) {
        editorAdapter.insertContent(`<div style="text-align: center;">${selection.text}</div>`);
    } else {
        editorAdapter.insertContent(`[form id="${formId}"]`);
    }
    emit('update:modelValue', editorAdapter.getHTML());
}

function openFileManager(): void {
    // TODO: implement file manager
}

function handleOpenImageModal(): void {
    const imgData = editorState.selectedImage.value;
    const pos = editorState.selectedImagePos.value;

    if (imgData) {
        imageEditData.value = {
            url: imgData.url,
            alt: imgData.alt,
            title: imgData.title,
            width: imgData.width ? String(imgData.width) : '',
            height: imgData.height ? String(imgData.height) : '',
            align: imgData.styleProps.align || '',
            float: imgData.styleProps.float || '',
            margin: imgData.styleProps.margin ? String(imgData.styleProps.margin) : '',
            _pos: pos,
        };
    } else {
        imageEditData.value = null;
    }

    imageModalKey.value++;
    showImageModal.value = true;
}

function closeImageModal(): void {
    showImageModal.value = false;
    imageEditData.value = null;
}

function handleImageInsert(data: any): void {
    const { url, alt, title, width, height, align, float, margin, oldUrl, _pos } = data;

    const imageData = ImageData.create({
        url,
        alt: alt || '',
        title: title || '',
        width: width || null,
        height: height || null,
        align: align || undefined,
        float: float || undefined,
        margin: margin || undefined,
    });

    if (oldUrl) {
        editorState.updateImage(oldUrl, imageData);
    } else {
        editorState.insertImage(imageData, _pos ?? undefined);
    }

    emit('update:modelValue', editorAdapter.getHTML());
    closeImageModal();
}

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

    editorState.clearImageSelection();
    selectedImageAlign.value = '';
    selectedImageFloat.value = '';

    ed.chain().focus().setTextAlign(align).run();
    emit('update:modelValue', editorAdapter.getHTML());
}

function handleOpenLinkModal(): void {
    const linkData = editorState.selectedLink.value;
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

function setLinkOnSelection(url: string, linkText: string, target: string, title: string): void {
    const linkData = LinkData.create({ url, text: linkText, target, title });
    editorState.insertLink(linkData);
    emit('update:modelValue', editorAdapter.getHTML());
}

function updateExistingLink(data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }): void {
    const linkData = LinkData.create({
        url: data.newUrl,
        text: data.newText,
        target: data.newTarget,
        title: data.newTitle,
        oldText: data.oldText,
    });
    editorState.updateLink(data.oldText, linkData);
    emit('update:modelValue', editorAdapter.getHTML());
}

function getHTML(): string {
    return editorAdapter.getHTML();
}

function getCursorPosition(): number {
    return editorAdapter.getSelection().from;
}

function insertContent(html: string, position?: number): void {
    if (typeof html !== 'string') return;

    const sanitizedHtml = DOMPurify.sanitize(html, {
        ALLOWED_TAGS: [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            'a', 'img', 'div', 'span',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'section', 'article', 'header', 'footer', 'nav',
        ],
        ALLOWED_ATTR: ['href', 'target', 'title', 'src', 'alt', 'width', 'height', 'style', 'class', 'id', 'data-align', 'data-float'],
        ALLOW_DATA_ATTR: false,
        ADD_URI_SAFE_ATTR: ['src', 'href'],
    });

    editorAdapter.insertContent(sanitizedHtml, position);
    emit('update:modelValue', editorAdapter.getHTML());
}

defineExpose({
    setLinkOnSelection,
    insertContent,
    updateImage: (oldUrl: string, newData: any) => {
        const imageData = ImageData.create({
            url: newData.url,
            alt: newData.alt || '',
            title: newData.title || '',
            width: newData.width || null,
            height: newData.height || null,
            align: newData.align || undefined,
            float: newData.float || undefined,
            margin: newData.margin || undefined,
        });
        editorState.updateImage(oldUrl, imageData);
        emit('update:modelValue', editorAdapter.getHTML());
    },
    updateExistingLink,
    insertFormShortcode,
    getCursorPosition,
    getHTML,
});

let clickHandler: ((e: MouseEvent) => void) | null = null;
let resizeEndHandler: ((e: Event) => void) | null = null;

onMounted(async () => {
    await nextTick();
    if (!editorElement.value) return;

    editor = new Editor({
        element: editorElement.value,
        extensions: [
            StarterKit.configure({
                link: false,
                underline: false,
                strike: false,
            }),
            PreserveAttributes,
            CustomDiv,
            ResizableImage,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: { class: 'text-blue-600 underline' },
            }),
            Underline,
            Strike,
            TextAlign.configure({ types: ['heading', 'paragraph'] }),
            CleanPaste,
        ],
        content: props.modelValue || '<p>Начните писать здесь...</p>',
        editorProps: {
            transformPastedHTML(html: string) {
                return html
                    .replace(/style="[^"]*"/g, '')
                    .replace(/class="[^"]*"/g, '')
                    .replace(/mso-[^=]+="[^"]*"/g, '')
                    .replace(/mso-[^=]+=[^ >]+/g, '')
                    .replace(/<span[^>]*>/g, '')
                    .replace(/<\/span>/g, '')
                    .replace(/<font[^>]*>/g, '')
                    .replace(/<\/font>/g, '');
            },
        },
        onUpdate: ({ editor: ed }) => {
            emit('update:modelValue', ed.getHTML());
        },
    });

    editorAdapter.init(editor);
    imageAdapter.init(editor);
    linkAdapter.init(editor);

    clickHandler = (e: MouseEvent) => {
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
            editorState.clearLinkSelection();
            editorState.clearImageSelection();
            selectedImageAlign.value = '';
            selectedImageFloat.value = '';
            return;
        }

        if (link) {
            e.preventDefault();
            const linkData = LinkData.create({
                url: link.getAttribute('href') || '',
                text: link.innerText,
                target: link.getAttribute('target') || '_self',
                title: link.getAttribute('title') || '',
                oldText: link.innerText,
            });
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            link.classList.add('selected-link');
            editorState.selectLink(linkData);
            editorState.clearImageSelection();
            selectedImageAlign.value = '';
            selectedImageFloat.value = '';
            return;
        }

        if (img) {
            e.preventDefault();
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
            editorState.clearLinkSelection();

            const wr = img.closest('.resize-wrapper');
            selectedImageAlign.value = wr?.getAttribute('data-align') || '';
            selectedImageFloat.value = wr?.getAttribute('data-float') || '';
            return;
        }

        editorState.clearLinkSelection();
        editorState.clearImageSelection();
        selectedImageAlign.value = '';
        selectedImageFloat.value = '';
    };

    document.addEventListener('click', clickHandler);

    resizeEndHandler = (e: Event) => {
        const detail = (e as CustomEvent).detail;
        if (!detail || typeof detail.pos !== 'number') return;

        const width = parseInt(detail.width, 10);
        const height = parseInt(detail.height, 10);
        if (isNaN(width) || isNaN(height)) return;

        const node = editorAdapter.getNodeAt(detail.pos);
        if (!node) return;

        const currentStyle = node.attrs.style || '';
        const cleanStyle = currentStyle
            .replace(/width:\s*\d+px;?/, '')
            .replace(/height:\s*\d+px;?/, '')
            .trim();

        const newStyle = `width: ${width}px; height: ${height}px;${cleanStyle ? ' ' + cleanStyle : ''}`;

        editorAdapter.updateNode(detail.pos, {
            ...node.attrs,
            width: String(width),
            height: String(height),
            style: newStyle.trim(),
        });

        emit('update:modelValue', editorAdapter.getHTML());
    };
    document.addEventListener('image-resize-end', resizeEndHandler);
});

watch(() => props.modelValue, (newValue) => {
    if (contentUpdateTimeout) clearTimeout(contentUpdateTimeout);
    contentUpdateTimeout = setTimeout(() => {
        if (editor && newValue !== editor.getHTML()) {
            editor.commands.setContent(newValue || '<p></p>');
        }
        contentUpdateTimeout = null;
    }, 300);
}, { immediate: true });

onBeforeUnmount(() => {
    if (clickHandler) {
        document.removeEventListener('click', clickHandler);
        clickHandler = null;
    }
    if (resizeEndHandler) {
        document.removeEventListener('image-resize-end', resizeEndHandler);
        resizeEndHandler = null;
    }
    if (contentUpdateTimeout) {
        clearTimeout(contentUpdateTimeout);
        contentUpdateTimeout = null;
    }
    editorAdapter.destroy();
});
</script>

<style>
@import '../../../../css/editor.css';
</style>
