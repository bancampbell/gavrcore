<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
        <!-- Панель инструментов -->
        <div class="border-b border-gray-200 bg-gray-50 p-2 flex flex-wrap gap-1 sticky top-0 z-10">
            <button
                @click="editor?.chain().focus().toggleBold().run()"
                :class="{ 'bg-gray-200': editor?.isActive('bold') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Жирный"
            >
                <span class="font-bold">B</span>
            </button>
            <button
                @click="editor?.chain().focus().toggleItalic().run()"
                :class="{ 'bg-gray-200': editor?.isActive('italic') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Курсив"
            >
                <span class="italic">I</span>
            </button>
            <button
                @click="editor?.chain().focus().toggleUnderline().run()"
                :class="{ 'bg-gray-200': editor?.isActive('underline') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Подчёркнутый"
            >
                <span class="underline">U</span>
            </button>
            <button
                @click="editor?.chain().focus().toggleStrike().run()"
                :class="{ 'bg-gray-200': editor?.isActive('strike') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Зачёркнутый"
            >
                <span class="line-through">S</span>
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="{ 'bg-gray-200': editor?.isActive('heading', { level: 1 }) }"
                class="px-3 h-8 rounded hover:bg-gray-200 transition text-sm font-bold"
                title="Заголовок H1"
            >
                H1
            </button>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-gray-200': editor?.isActive('heading', { level: 2 }) }"
                class="px-3 h-8 rounded hover:bg-gray-200 transition text-sm font-semibold"
                title="Заголовок H2"
            >
                H2
            </button>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'bg-gray-200': editor?.isActive('heading', { level: 3 }) }"
                class="px-3 h-8 rounded hover:bg-gray-200 transition text-sm"
                title="Заголовок H3"
            >
                H3
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="editor?.chain().focus().setTextAlign('left').run()"
                :class="{ 'bg-gray-200': editor?.isActive({ textAlign: 'left' }) }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="По левому краю"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" />
                </svg>
            </button>
            <button
                @click="editor?.chain().focus().setTextAlign('center').run()"
                :class="{ 'bg-gray-200': editor?.isActive({ textAlign: 'center' }) }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="По центру"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16" />
                </svg>
            </button>
            <button
                @click="editor?.chain().focus().setTextAlign('right').run()"
                :class="{ 'bg-gray-200': editor?.isActive({ textAlign: 'right' }) }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="По правому краю"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16" />
                </svg>
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="editor?.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-gray-200': editor?.isActive('bulletList') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Маркированный список"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <button
                @click="editor?.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-gray-200': editor?.isActive('orderedList') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Нумерованный список"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h14M7 12h14M7 18h14" />
                </svg>
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="emit('openLinkModal')"
                :class="{ 'bg-gray-200': editor?.isActive('link') }"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Ссылка"
            >
                🔗
            </button>
            <button
                @click="addImage"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Изображение"
            >
                🖼️
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="editor?.chain().focus().undo().run()"
                :disabled="!editor?.can().undo()"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center disabled:opacity-30"
                title="Отменить"
            >
                ↩️
            </button>
            <button
                @click="editor?.chain().focus().redo().run()"
                :disabled="!editor?.can().redo()"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center disabled:opacity-30"
                title="Вернуть"
            >
                ↪️
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="editor?.chain().focus().clearNodes().unsetAllMarks().run()"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Очистить форматирование"
            >
                ✖️
            </button>
            <button
                @click="toggleHtml"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="HTML код"
            >
                &lt;/&gt;
            </button>
            <div class="w-px h-6 bg-gray-300 mx-1"></div>
            <button
                @click="openFileManager"
                class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
                title="Файловый менеджер"
            >
                📎
            </button>
        </div>

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
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import TextAlign from '@tiptap/extension-text-align';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'openLinkModal'): void;
    (e: 'editLink', data: { oldText: string; url: string; text: string; target: string; title: string }): void;
}>();

const editorElement = ref<HTMLElement>();
const showHtml = ref(false);
const htmlContent = ref('');
let editor: Editor | null = null;

// Публичный метод для вставки ссылки на выделение
const setLinkOnSelection = (url: string, linkText: string, target: string, title: string) => {
    if (!editor) return;

    const { from, to } = editor.state.selection;
    const hasSelection = from !== to;

    if (hasSelection) {
        // Сохраняем выделенный текст
        const selectedText = editor.state.doc.textBetween(from, to);
        const linkHtml = `<a href="${url}" target="${target}" title="${title}">${selectedText}</a>`;
        editor.commands.insertContent(linkHtml);
    } else {
        const linkHtml = `<a href="${url}" target="${target}" title="${title}">${linkText || url}</a>`;
        editor.commands.insertContent(linkHtml);
    }
};

// Открываем функцию наружу
defineExpose({
    setLinkOnSelection
});

const getLinkData = (link: HTMLAnchorElement) => {
    return {
        oldText: link.innerText,
        url: link.getAttribute('href') || '',
        text: link.innerText,
        target: link.getAttribute('target') || '',
        title: link.getAttribute('title') || ''
    };
};

const handleLinkMouseDown = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    const link = target.closest('a');
    if (link) {
        e.preventDefault();
        e.stopPropagation();
        emit('editLink', getLinkData(link));
    }
};

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
            Image,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: 'text-blue-600 underline',
                },
            }),
            Underline,
            Strike,
            TextAlign.configure({
                types: ['heading', 'paragraph'],
            }),
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
        editorContainer.addEventListener('mousedown', handleLinkMouseDown);
    }
});

watch(() => props.modelValue, (newValue) => {
    if (editor && !showHtml.value && newValue !== editor.getHTML()) {
        editor.commands.setContent(newValue || '<p></p>');
    }
});

const addImage = () => {
    const url = window.prompt('Введите URL изображения:');
    if (url && editor) {
        editor.chain().focus().setImage({ src: url }).run();
    }
};

const openFileManager = () => {
    console.log('Открыть файловый менеджер');
    alert('Файловый менеджер будет здесь');
};

const toggleHtml = () => {
    if (!showHtml.value) {
        htmlContent.value = editor?.getHTML() || '';
        showHtml.value = true;
    } else {
        showHtml.value = false;
    }
};

const applyHtml = () => {
    if (editor) {
        editor.commands.setContent(htmlContent.value);
        emit('update:modelValue', htmlContent.value);
    }
    showHtml.value = false;
};

const cancelHtml = () => {
    showHtml.value = false;
};

onBeforeUnmount(() => {
    const editorContainer = document.querySelector('.tiptap');
    if (editorContainer) {
        editorContainer.removeEventListener('mousedown', handleLinkMouseDown);
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
}

.tiptap a {
    color: #2563eb;
    text-decoration: underline;
    cursor: pointer;
}

.tiptap ul,
.tiptap ol {
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
