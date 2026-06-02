<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
        <Toolbar
            :editor="editor"
            :selected-link-data="selectedLinkData"
            :selected-image-align="selectedImageAlign"
            :align-image-left="() => imageHandlers.alignImageLeft(editor!)"
            :center-image="() => imageHandlers.centerImage(editor!)"
            :align-image-right="() => imageHandlers.alignImageRight(editor!)"
            :open-link-modal="openLinkModal"
            :open-image-modal="() => imageHandlers.openImageModal(emit)"
            :toggle-html="toggleHtml"
            :open-file-manager="openFileManager"
        />

        <div v-show="!showHtml" class="flex-1 overflow-auto">
            <div class="tiptap p-4 h-full" ref="editorElement"></div>
        </div>

        <div v-show="showHtml" class="flex-1 p-4">
            <textarea
                v-model="htmlContent"
                class="w-full h-full font-mono text-sm border border-gray-300 rounded p-2 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                rows="12"
            ></textarea>
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
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
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

const props = defineProps<EditorProps>();
const emit = defineEmits<EditorEmits>();

const editorElement = ref<HTMLElement>();
const showHtml = ref(false);
const htmlContent = ref('');
let editor: Editor | null = null;

const { toggleHtml, applyHtml, cancelHtml } = useHtmlMode(
    { value: editor },
    showHtml,
    htmlContent,
    emit
);

const {
    selectedLinkData,
    handleLinkMouseDown,
    openLinkModal,
    setLinkOnSelection: linkHandlersSetLink,
} = useLinkHandlers(emit);

const imageHandlers = useImageHandlers();
const { selectedImageAlign, handleImageClick, updateImage: imageHandlersUpdate } = imageHandlers;

const setLinkOnSelection = (url: string, linkText: string, target: string, title: string) => {
    if (editor) {
        linkHandlersSetLink(editor, url, linkText, target, title);
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
    updateImage
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
            StarterKit,
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
    const editorContainer = document.querySelector('.tiptap');
    if (editorContainer) {
        editorContainer.addEventListener('mousedown', handleLinkMouseDown, true);
        editorContainer.addEventListener('click', handleImageClick, true);
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
