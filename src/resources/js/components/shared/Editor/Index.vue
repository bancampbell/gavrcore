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
        />

        <div v-show="!showHtml" class="flex-1 overflow-auto">
            <div class="tiptap p-4 h-full" ref="editorElement"></div>
        </div>

        <div v-show="showHtml" class="flex-1 p-4 overflow-hidden">
            <div ref="codeEditorRef" class="h-full w-full"></div>
            <div class="mt-2 flex justify-end gap-2">
                <button @click="applyHtml" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">
                    Применить
                </button>
                <button @click="cancelHtml" class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300">
                    Отмена
                </button>
            </div>
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
import { useHtmlMode, useLinkHandlers, useImageHandlers } from './composables';
import type { EditorProps, EditorEmits } from './types/editor';
import GallerySelectModal from '@/components/shared/GallerySelectModal.vue';
import FormSelectModal from '@/components/shared/FormSelectModal.vue';

// CodeMirror
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { EditorView as EditorViewExt } from '@codemirror/view';

// Prettier
import { format } from 'prettier/standalone';
import parserHtml from 'prettier/plugins/html';

const props = defineProps<EditorProps>();
const emit = defineEmits<EditorEmits>();

const editorElement = ref<HTMLElement>();
const showHtml = ref(false);
const htmlContent = ref('');
const codeEditorRef = ref<HTMLElement>();
let editor: Editor | null = null;
let codeEditorView: EditorView | null = null;

const editorRef = ref(editor) as VueRef<Editor | null>;

const showGalleryModal = ref(false);
const showFormModal = ref(false);

const handleOpenGalleryModal = () => {
    showGalleryModal.value = true;
};

const insertGallery = (gallery: any) => {
    if (!editor) return;
    const shortcode = `[gallery id="${gallery.id}" name="${gallery.name || gallery.title}"]`;
    editor.commands.insertContent(shortcode);
    showGalleryModal.value = false;
};

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

const { toggleHtml, applyHtml, cancelHtml } = useHtmlMode(
    editorRef,
    showHtml,
    htmlContent,
    (event, value) => {
        if (event === 'update:modelValue') {
            emit('update:modelValue', value);
        }
    }
);

const linkHandlers = useLinkHandlers(emit);
const { clearSelection: clearLinkSelection } = linkHandlers;

const imageHandlers = useImageHandlers();
const { clearSelection: clearImageSelection } = imageHandlers;

let clickHandler: ((e: MouseEvent) => void) | null = null;

watch(editor, (newEditor) => {
    editorRef.value = newEditor;
});

watch(showHtml, async (newVal) => {
    if (newVal) {
        await nextTick();
        if (codeEditorRef.value && !codeEditorView) {
            let formattedHtml = htmlContent.value;
            try {
                formattedHtml = await format(htmlContent.value, {
                    parser: 'html',
                    plugins: [parserHtml],
                    printWidth: 80,
                    tabWidth: 2,
                    useTabs: false,
                });
            } catch (e) {
                console.warn('HTML formatting failed:', e);
            }
            htmlContent.value = formattedHtml;

            codeEditorView = new EditorView({
                doc: formattedHtml,
                extensions: [
                    basicSetup,
                    html(),
                    EditorViewExt.lineWrapping,
                    EditorView.updateListener.of((update) => {
                        if (update.docChanged) {
                            htmlContent.value = update.state.doc.toString();
                        }
                    }),
                ],
                parent: codeEditorRef.value,
            });
        }
    } else {
        if (codeEditorView) {
            codeEditorView.destroy();
            codeEditorView = null;
        }
    }
});

watch(htmlContent, (newVal) => {
    if (codeEditorView && newVal !== codeEditorView.state.doc.toString()) {
        const transaction = codeEditorView.state.update({
            changes: {
                from: 0,
                to: codeEditorView.state.doc.length,
                insert: newVal,
            },
        });
        codeEditorView.dispatch(transaction);
    }
});

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

defineExpose({
    setLinkOnSelection,
    insertContent,
    updateImage,
    updateExistingLink,
    insertFormShortcode,
    getCursorPosition,
});

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
            if (!showHtml.value) {
                emit('update:modelValue', editor.getHTML());
            }
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
            const imgData = {
                url: img.src,
                alt: img.alt || '',
                title: img.title || '',
                width: img.style.width || '',
                height: img.style.height || '',
                align: '',
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

watch(() => props.modelValue, (newValue) => {
    if (editor && !showHtml.value && newValue !== editor.getHTML()) {
        editor.commands.setContent(newValue || '<p></p>');
    }
});

onBeforeUnmount(() => {
    if (clickHandler) {
        document.removeEventListener('click', clickHandler);
        clickHandler = null;
    }
    editor?.destroy();
    if (codeEditorView) {
        codeEditorView.destroy();
        codeEditorView = null;
    }
});
</script>

<style>
.tiptap {
    min-height: 100%;
    outline: none;
}
.tiptap:focus {
    outline: none;
}
.tiptap img {
    max-width: 100%;
    height: auto;
    cursor: pointer;
    transition: outline 0.2s;
}
.tiptap img.selected-image {
    outline: 3px solid #4f46e5;
    outline-offset: 2px;
}
.tiptap a {
    color: #2563eb;
    text-decoration: underline;
    cursor: pointer;
}
.tiptap a.selected-link {
    outline: 2px solid #4f46e5;
    background-color: rgba(79, 70, 229, 0.1);
}
.tiptap ul, .tiptap ol {
    padding-left: 1.5rem;
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}
.tiptap li {
    margin-top: 0.25rem;
    margin-bottom: 0.25rem;
}
.tiptap h1 {
    font-size: 2rem;
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.tiptap h2 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.tiptap h3 {
    font-size: 1.25rem;
    font-weight: bold;
    margin-top: 0.75rem;
    margin-bottom: 0.5rem;
}
.tiptap p {
    margin-bottom: 0.5rem;
}
</style>
