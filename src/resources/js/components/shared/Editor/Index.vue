<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
        <Toolbar
            :editor="editor"
            :selected-link-data="linkHandlers.selectedLinkData.value"
            :selected-image-data="imageHandlers.selectedImageData.value"
            :selected-image-align="imageHandlers.selectedImageAlign.value"
            :align-image-left="() => imageHandlers.alignImageLeft(editor!)"
            :center-image="() => imageHandlers.centerImage(editor!)"
            :align-image-right="() => imageHandlers.alignImageRight(editor!)"
            :open-link-modal="linkHandlers.openLinkModal"
            :open-image-modal="() => imageHandlers.openImageModal(emit)"
            :open-gallery-modal="handleOpenGalleryModal"
            :toggle-html="toggleHtml"
            :open-file-manager="openFileManager"
            :open-form-modal="openFormModal"
            :is-raw-html-mode="isRawHtmlMode"
            @toggle-raw-html="emit('toggleRawHtml')"
        />

        <div class="flex-1 overflow-auto">
            <div class="tiptap p-4 h-full" ref="editorElement"></div>
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
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onBeforeUnmount, nextTick, type Ref as VueRef } from 'vue';
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
import { useLinkHandlers, useImageHandlers } from './composables';
import type { EditorProps, EditorEmits } from './types/editor';
import GallerySelectModal from '@/components/shared/GallerySelectModal.vue';
import FormSelectModal from '@/components/shared/FormSelectModal.vue';


const props = defineProps<EditorProps>();
const emit = defineEmits<EditorEmits>();

const editorElement = ref<HTMLElement>();
let editor: Editor | null = null;

const editorRef = ref(editor) as VueRef<Editor | null>;

const showGalleryModal = ref(false);
const showFormModal = ref(false);
const isRawHtmlMode = ref(false);

// ===== HTML РЕЖИМ =====
const toggleHtml = () => {
    emit('toggleRawHtml');
};

const getHTML = () => {
    return editor?.getHTML() || '';
};

// ===== ГАЛЕРЕЯ =====
const handleOpenGalleryModal = () => {
    showGalleryModal.value = true;
};

const insertGallery = (gallery: any) => {
    if (!editor) return;
    const shortcode = `[gallery id="${gallery.id}" name="${gallery.name || gallery.title}"]`;
    editor.commands.insertContent(shortcode);
    showGalleryModal.value = false;
};

// ===== ФОРМЫ =====
const openFormModal = () => {
    showFormModal.value = true;
};

const insertForm = (form: any) => {
    if (!editor) return;
    const shortcode = `[form id="${form.id}"]`;
    editor.commands.insertContent(shortcode);
    showFormModal.value = false;
};

const insertFormShortcode = (formId: number) => {
    if (!editor) return;
    const { from, to } = editor.state.selection;
    const hasSelection = from !== to;
    if (hasSelection) {
        const selectedText = editor.state.doc.textBetween(from, to);
        editor.commands.insertContent(`<div style="text-align: center;">${selectedText}</div>`);
    } else {
        editor.commands.insertContent(`[form id="${formId}"]`);
    }
};

const openFileManager = () => {
    // TODO: implement file manager
};

// ===== ССЫЛКИ =====
const linkHandlers = useLinkHandlers(emit);
const { clearSelection: clearLinkSelection } = linkHandlers;

// ===== ИЗОБРАЖЕНИЯ =====
const imageHandlers = useImageHandlers();
const { clearSelection: clearImageSelection } = imageHandlers;

const handleOpenLinkModal = () => {
    if (editor && linkHandlers.selectedLinkData.value) {
        linkHandlers.saveLinkPosition(editor, linkHandlers.selectedLinkData.value.oldText);
        linkHandlers.openLinkModal();
    } else if (editor) {
        const { from, to } = editor.state.selection;
        const hasSelection = from !== to;
        if (hasSelection) {
            const selectedText = editor.state.doc.textBetween(from, to);
            linkHandlers.openLinkModal(selectedText);
        } else {
            linkHandlers.openLinkModal('');
        }
    } else {
        linkHandlers.openLinkModal('');
    }
};

const setLinkOnSelection = (url: string, linkText: string, target: string, title: string) => {
    if (editor) {
        linkHandlers.setLinkOnSelection(editor, url, linkText, target, title);
    }
};

const updateExistingLink = (data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }) => {
    if (editor) {
        linkHandlers.updateExistingLink(editor, data.oldText, data.newUrl, data.newText, data.newTarget, data.newTitle);
    }
};

const updateImage = (oldUrl: string, newData: any) => {
    if (editor) {
        imageHandlers.updateImage(editor, oldUrl, newData);
    }
};

const getCursorPosition = () => {
    if (editor) {
        return editor.state.selection.from;
    }
    return 0;
};

const insertContent = (html: string, position?: number) => {
    if (typeof html !== 'string') {
        console.error('[Editor] insertContent received non-string:', html);
        return;
    }

    if (editor) {
        const pos = position ?? editor.state.selection.from;
        editor.commands.focus();
        editor.commands.setTextSelection(pos);
        editor.commands.insertContent(html);
        emit('update:modelValue', editor.getHTML());
    }
};

// ===== FIX СТИЛЕЙ ИЗОБРАЖЕНИЙ =====
const fixImageStyles = () => {
    if (!editorElement.value) return;
    const imgs = editorElement.value.querySelectorAll('img');
    imgs.forEach((img: HTMLImageElement) => {
        if (img.hasAttribute('data-lightbox')) {
            img.removeAttribute('data-lightbox');
        }
        const style = img.getAttribute('style') || '';
        if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
            let newStyle = style
                .replace(/max-width:\s*[^;]+;?/g, '')
                .replace(/pointer-events:\s*[^;]+;?/g, '')
                .replace(/;\s*;/g, ';')
                .trim();
            if (newStyle.endsWith(';')) newStyle = newStyle.slice(0, -1);
            if (newStyle !== style) {
                img.setAttribute('style', newStyle);
            }
        }
    });
};

defineExpose({
    setLinkOnSelection,
    insertContent,
    updateImage,
    updateExistingLink,
    insertFormShortcode,
    getCursorPosition,
    getHTML,
});

// ===== КЛИК ХЕНДЛЕР =====
let clickHandler: ((e: MouseEvent) => void) | null = null;

onMounted(async () => {
    await nextTick();
    if (!editorElement.value) {
        console.error('Editor element not found!');
        return;
    }

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
        ],
        content: props.modelValue || '<p>Начните писать здесь...</p>',
        onUpdate: ({ editor }) => {
            emit('update:modelValue', editor.getHTML());
            nextTick(fixImageStyles);
        },
    });

    editorRef.value = editor;

    document.addEventListener('image-selected', (e: any) => {
        imageHandlers.selectedImageData.value = e.detail;
    });

    if (clickHandler) {
        document.removeEventListener('click', clickHandler);
    }

    clickHandler = (e: MouseEvent) => {
        const target = e.target as HTMLElement;
        const link = target.closest('a');
        const img = target.closest('img');
        const isInsideTiptap = target.closest('.tiptap');

        if (!isInsideTiptap) {
            clearLinkSelection();
            clearImageSelection();
            return;
        }

        if (link) {
            e.preventDefault();
            const linkData = {
                oldText: link.innerText,
                url: link.getAttribute('href') || '',
                text: link.innerText,
                target: link.getAttribute('target') || '_self',
                title: link.getAttribute('title') || '',
            };
            linkHandlers.selectedLinkData.value = linkData;
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            link.classList.add('selected-link');
            clearImageSelection();
            return;
        }

        if (img) {
            e.preventDefault();
            const style = img.getAttribute('style') || '';
            let align = '';
            if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
                align = 'center';
            } else if (style.includes('margin-left: 0')) {
                align = 'left';
            } else if (style.includes('margin-right: 0')) {
                align = 'right';
            }

            const imgData = {
                url: img.src,
                alt: img.alt || '',
                title: img.title || '',
                width: img.style.width || '',
                height: img.style.height || '',
                align: align,
            };
            imageHandlers.selectedImageData.value = imgData;
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            img.classList.add('selected-image');
            clearLinkSelection();
            return;
        }

        clearLinkSelection();
        clearImageSelection();
    };

    document.addEventListener('click', clickHandler);
});

// ===== СЛЕДИМ ЗА ИЗМЕНЕНИЕМ modelValue ИЗВНЕ =====
watch(() => props.modelValue, (newValue) => {
    console.log('[Editor] watch modelValue triggered, length:', newValue?.length || 0);
    if (editor && newValue !== editor.getHTML()) {
        console.log('[Editor] updating content, length:', newValue?.length || 0);
        editor.commands.setContent(newValue || '<p></p>');
        nextTick(fixImageStyles);
    }
}, { immediate: true });

onBeforeUnmount(() => {
    if (clickHandler) {
        document.removeEventListener('click', clickHandler);
        clickHandler = null;
    }
    editor?.destroy();
});
</script>

<style>
@import '../../../../css/editor.css';
</style>
