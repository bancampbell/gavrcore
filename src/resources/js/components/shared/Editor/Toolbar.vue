<template>
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
            @click="alignImageLeft"
            :class="{ 'bg-blue-100 text-blue-700': selectedImageAlign === 'left' }"
            class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
            title="По левому краю"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16" />
            </svg>
        </button>
        <button
            @click="centerImage"
            :class="{ 'bg-blue-100 text-blue-700': selectedImageAlign === 'center' }"
            class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
            title="По центру"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16" />
            </svg>
        </button>
        <button
            @click="alignImageRight"
            :class="{ 'bg-blue-100 text-blue-700': selectedImageAlign === 'right' }"
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
            @click="openLinkModal"
            :class="{ 'bg-blue-100 text-blue-700': selectedLinkData !== null }"
            class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
            title="Ссылка"
        >
            🔗
        </button>
        <button
            @click="openImageModal"
            class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
            title="Изображение"
        >
            🖼️
        </button>
        <button
            @click="openGalleryModal"
            class="w-8 h-8 rounded hover:bg-gray-200 transition flex items-center justify-center"
            title="Вставить галерею"
        >
            📷
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
</template>

<script setup lang="ts">
import { Editor } from '@tiptap/core';
import type { LinkData } from './types/editor';

defineProps<{
    editor: Editor | null;
    selectedLinkData: LinkData | null;
    selectedImageAlign: string;
    alignImageLeft: () => void;
    centerImage: () => void;
    alignImageRight: () => void;
    openLinkModal: () => void;
    openImageModal: () => void;
    openGalleryModal: () => void;
    toggleHtml: () => void;
    openFileManager: () => void;
}>();
</script>
