<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
        <Toolbar
            :editor="editor"
            :selected-link-data="selectedLinkData"
            :selected-image-align="selectedImageAlign"
            :align-image-left="() => imageHandlers.alignImageLeft(editor!)"
            :center-image="() => imageHandlers.centerImage(editor!)"
            :align-image-right="() => imageHandlers.alignImageRight(editor!)"
            :open-link-modal="handleOpenLinkModal"
            :open-image-modal="() => imageHandlers.openImageModal(emit)"
            :open-gallery-modal="handleOpenGalleryModal"
            :toggle-html="toggleHtml"
            :open-file-manager="openFileManager"
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

// CodeMirror
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { EditorView as EditorViewExt } from '@codemirror/view';

// Prettier для форматирования HTML
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

const {
    selectedLinkData,
    handleLinkMouseDown,
    openLinkModal,
    setLinkOnSelection: linkHandlersSetLink,
    updateExistingLink: linkHandlersUpdateExistingLink,
    clearSelectedLink,
    saveLinkPosition,
} = useLinkHandlers(emit);

const imageHandlers = useImageHandlers();
const { selectedImageAlign, handleImageClick, updateImage: imageHandlersUpdate } = imageHandlers;

watch(editor, (newEditor) => {
    editorRef.value = newEditor;
});

// При открытии HTML режима создаем CodeMirror с форматированием
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

// Синхронизация при изменении htmlContent извне
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
    if (editor && selectedLinkData.value) {
        saveLinkPosition(editor, selectedLinkData.value.oldText);
        openLinkModal();
    } else if (editor) {
        const { from, to } = editor.state.selection;
        const hasSelection = from !== to;
        if (hasSelection) {
            const selectedText = editor.state.doc.textBetween(from, to);
            openLinkModal(selectedText);
        } else {
            openLinkModal('');
        }
    } else {
        openLinkModal('');
    }
};

const handleOpenGalleryModal = () => {
    emit('open-gallery-modal');
};

const setLinkOnSelection = (url: string, linkText: string, target: string, title: string) => {
    if (editor) {
        linkHandlersSetLink(editor, url, linkText, target, title);
    }
};

const updateExistingLink = (data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }) => {
    if (editor) {
        linkHandlersUpdateExistingLink(editor, data.oldText, data.newUrl, data.newText, data.newTarget, data.newTitle);
    }
};

const updateImage = (oldUrl: string, newData: any) => {
    if (editor) {
        imageHandlersUpdate(editor, oldUrl, newData);
    }
};

const insertContent = (html: string) => {
    if (editor) {
        editor.commands.insertContent(html);
        emit('update:modelValue', editor.getHTML());
    }
};

const openFileManager = () => {
    // TODO: implement file manager
};

defineExpose({
    setLinkOnSelection,
    insertContent,
    updateImage,
    updateExistingLink
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

    const editorContainer = document.querySelector('.tiptap');
    if (editorContainer) {
        editorContainer.addEventListener('mousedown', (e) => handleLinkMouseDown(e as MouseEvent), true);
        editorContainer.addEventListener('click', (e) => handleImageClick(e as MouseEvent), true);
    }
});

watch(() => props.modelValue, (newValue) => {
    if (editor && !showHtml.value && newValue !== editor.getHTML()) {
        editor.commands.setContent(newValue || '<p></p>');
    }
});

onBeforeUnmount(() => {
    const editorContainer = document.querySelector('.tiptap');
    if (editorContainer) {
        editorContainer.removeEventListener('mousedown', handleLinkMouseDown, true);
        editorContainer.removeEventListener('click', handleImageClick, true);
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

/* CodeMirror стили — светлая тема */
.cm-editor {
    height: 100% !important;
    border-radius: 0.5rem !important;
    overflow: hidden !important;
    font-size: 13px !important;
    background: #ffffff !important;
}
.cm-scroller {
    overflow: auto !important;
}

/* Светлая тема для подсветки */
.cm-editor .cm-content {
    color: #24292e !important;
    font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
}

/* HTML теги — синие */
.cm-editor .ͼ1 .cm-tag {
    color: #0550ae !important;
}

/* Атрибуты — красные */
.cm-editor .ͼ1 .cm-attribute {
    color: #8250df !important;
}

/* Значения атрибутов — зеленые */
.cm-editor .ͼ1 .cm-string {
    color: #0a3069 !important;
}

/* Комментарии — серые */
.cm-editor .ͼ1 .cm-comment {
    color: #6e7781 !important;
}

/* Ключевые слова — синие */
.cm-editor .ͼ1 .cm-keyword {
    color: #0550ae !important;
}

/* Операторы — черные */
.cm-editor .ͼ1 .cm-operator {
    color: #24292e !important;
}
</style>
