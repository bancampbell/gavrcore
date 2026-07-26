<template>
    <div class="border-b border-slate-200 bg-white px-3 py-2.5 flex flex-wrap items-center gap-1 sticky top-0 z-10 shadow-sm">
        <!-- Форматирование текста -->
        <div class="flex items-center gap-0.5">
            <button
                @click="editor?.chain().focus().toggleBold().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('bold'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('bold') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Жирный (Ctrl+B)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
            </button>
            <button
                @click="editor?.chain().focus().toggleItalic().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('italic'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('italic') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Курсив (Ctrl+I)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="19" y1="4" x2="10" y2="4" stroke-width="2.5" stroke-linecap="round"/><line x1="14" y1="20" x2="5" y2="20" stroke-width="2.5" stroke-linecap="round"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2.5" stroke-linecap="round"/></svg>
            </button>
            <button
                @click="editor?.chain().focus().toggleUnderline().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('underline'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('underline') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Подчёркнутый (Ctrl+U)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3" stroke-width="2.5" stroke-linecap="round"/><line x1="4" y1="21" x2="20" y2="21" stroke-width="2.5" stroke-linecap="round"/></svg>
            </button>
            <button
                @click="editor?.chain().focus().toggleStrike().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('strike'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('strike') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Зачёркнутый"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16 4H8a4 4 0 100 8h8a4 4 0 110 8H8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/><line x1="4" y1="12" x2="20" y2="12" stroke-width="2.5" stroke-linecap="round"/></svg>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Заголовки -->
        <div class="flex items-center gap-0.5">
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('heading', { level: 1 }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('heading', { level: 1 }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-sm font-bold"
                title="Заголовок 1"
            >H1</button>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('heading', { level: 2 }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('heading', { level: 2 }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-sm font-semibold"
                title="Заголовок 2"
            >H2</button>
            <button
                @click="editor?.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('heading', { level: 3 }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('heading', { level: 3 }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-sm"
                title="Заголовок 3"
            >H3</button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Выравнивание текста -->
        <div class="flex items-center gap-0.5">
            <button
                @click="alignTextLeft"
                :class="{ 'bg-blue-100 text-blue-700': editor?.isActive({ textAlign: 'left' }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive({ textAlign: 'left' }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Текст по левому краю"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"/></svg>
            </button>
            <button
                @click="alignTextCenter"
                :class="{ 'bg-blue-100 text-blue-700': editor?.isActive({ textAlign: 'center' }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive({ textAlign: 'center' }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Текст по центру"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M4 18h16"/></svg>
            </button>
            <button
                @click="alignTextRight"
                :class="{ 'bg-blue-100 text-blue-700': editor?.isActive({ textAlign: 'right' }), 'text-slate-600 hover:bg-slate-100': !editor?.isActive({ textAlign: 'right' }) }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Текст по правому краю"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M4 18h16"/></svg>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Изображения: выравнивание + обтекание -->
        <div class="flex items-center gap-0.5">
            <button
                @click="alignImageLeft"
                :class="{ 'bg-emerald-100 text-emerald-700': selectedImageAlign === 'left', 'text-slate-600 hover:bg-slate-100': selectedImageAlign !== 'left' }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Картинка слева"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M4 8h6M4 12h4M4 16h7"/></svg>
            </button>
            <button
                @click="centerImage"
                :class="{ 'bg-emerald-100 text-emerald-700': selectedImageAlign === 'center', 'text-slate-600 hover:bg-slate-100': selectedImageAlign !== 'center' }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Картинка по центру"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M7 8h6M8 12h4M7 16h7"/></svg>
            </button>
            <button
                @click="alignImageRight"
                :class="{ 'bg-emerald-100 text-emerald-700': selectedImageAlign === 'right', 'text-slate-600 hover:bg-slate-100': selectedImageAlign !== 'right' }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Картинка справа"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M10 8h6M12 12h4M10 16h7"/></svg>
            </button>
        </div>
        <div class="flex items-center gap-0.5">
            <button
                @click="floatImageLeft"
                :class="{ 'bg-amber-100 text-amber-700': selectedImageFloat === 'left', 'text-slate-600 hover:bg-slate-100': selectedImageFloat !== 'left' }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Обтекание слева"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="4" width="8" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M16 7h4M16 11h3M16 15h4"/></svg>
            </button>
            <button
                @click="floatImageRight"
                :class="{ 'bg-amber-100 text-amber-700': selectedImageFloat === 'right', 'text-slate-600 hover:bg-slate-100': selectedImageFloat !== 'right' }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Обтекание справа"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="12" y="4" width="8" height="16" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M4 7h4M5 11h3M4 15h4"/></svg>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Списки -->
        <div class="flex items-center gap-0.5">
            <button
                @click="editor?.chain().focus().toggleBulletList().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('bulletList'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('bulletList') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Маркированный список"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="7" r="2" stroke-width="2"/><circle cx="5" cy="12" r="2" stroke-width="2"/><circle cx="5" cy="17" r="2" stroke-width="2"/><line x1="11" y1="7" x2="19" y2="7" stroke-width="2" stroke-linecap="round"/><line x1="11" y1="12" x2="19" y2="12" stroke-width="2" stroke-linecap="round"/><line x1="11" y1="17" x2="19" y2="17" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button
                @click="editor?.chain().focus().toggleOrderedList().run()"
                :class="{ 'bg-slate-200 text-slate-900': editor?.isActive('orderedList'), 'text-slate-600 hover:bg-slate-100': !editor?.isActive('orderedList') }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Нумерованный список"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><text x="3" y="9" font-size="8" font-weight="bold" fill="currentColor" stroke="none">1</text><text x="3" y="16" font-size="8" font-weight="bold" fill="currentColor" stroke="none">2</text><text x="3" y="23" font-size="8" font-weight="bold" fill="currentColor" stroke="none">3</text><line x1="9" y1="7" x2="18" y2="7" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="14" x2="18" y2="14" stroke-width="2" stroke-linecap="round"/><line x1="9" y1="21" x2="18" y2="21" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Вставка -->
        <div class="flex items-center gap-0.5">
            <button
                @click="openLinkModal"
                :class="{ 'bg-blue-100 text-blue-700': selectedLinkData !== null, 'text-slate-600 hover:bg-slate-100': selectedLinkData === null }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Вставить ссылку"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </button>
            <button
                @click="openImageModal"
                :class="{ 'bg-blue-100 text-blue-700': selectedImageData !== null, 'text-slate-600 hover:bg-slate-100': selectedImageData === null }"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center"
                title="Вставить изображение"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15l-5-5L5 21"/></svg>
            </button>
            <button
                @click="openGalleryModal"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-slate-600 hover:bg-slate-100"
                title="Вставить галерею"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" stroke-width="2"/><rect x="7" y="7" width="4" height="4" rx="1" stroke-width="2"/><rect x="13" y="7" width="4" height="4" rx="1" stroke-width="2"/><rect x="7" y="13" width="4" height="4" rx="1" stroke-width="2"/><rect x="13" y="13" width="4" height="4" rx="1" stroke-width="2"/></svg>
            </button>
            <button
                @click="openFormModal"
                class="px-2.5 h-9 rounded-lg transition flex items-center justify-center gap-1.5 text-sm font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100"
                title="Вставить форму"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke-width="2"/></svg>
                <span>Форма</span>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- История -->
        <div class="flex items-center gap-0.5">
            <button
                @click="editor?.chain().focus().undo().run()"
                :disabled="!editor?.can().undo()"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30"
                title="Отменить (Ctrl+Z)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 010 10H9m-6-10l3-3m-3 3l3 3"/></svg>
            </button>
            <button
                @click="editor?.chain().focus().redo().run()"
                :disabled="!editor?.can().redo()"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-slate-600 hover:bg-slate-100 disabled:opacity-30"
                title="Вернуть (Ctrl+Shift+Z)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 000 10h4m6-10l-3-3m3 3l-3 3"/></svg>
            </button>
        </div>

        <div class="w-px h-5 bg-slate-300 mx-1.5"></div>

        <!-- Инструменты -->
        <div class="flex items-center gap-0.5">
            <button
                @click="editor?.chain().focus().clearNodes().unsetAllMarks().run()"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-slate-600 hover:bg-slate-100"
                title="Очистить форматирование"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M7 4h10l1 16H6L7 4z"/><line x1="10" y1="11" x2="14" y2="11" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <button
                @click="toggleHtml"
                class="px-2.5 h-9 rounded-lg transition flex items-center justify-center gap-1.5 text-sm font-medium"
                :class="isRawHtmlMode ? 'bg-purple-600 text-white' : 'text-purple-700 bg-purple-50 hover:bg-purple-100'"
                title="HTML режим"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polyline points="16 18 22 12 16 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><polyline points="8 6 2 12 8 18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>{{ isRawHtmlMode ? 'Визуально' : 'HTML' }}</span>
            </button>
            <button
                @click="openFileManager"
                class="w-9 h-9 rounded-lg transition flex items-center justify-center text-slate-600 hover:bg-slate-100"
                title="Файловый менеджер"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Editor } from '@tiptap/core';
import type { LinkData } from './types/editor';
import type { ImageData } from './types/editor';

defineProps<{
    editor: Editor | null;
    selectedLinkData: LinkData | null;
    selectedImageData: ImageData | null;
    selectedImageAlign: string;
    selectedImageFloat: string;
    alignImageLeft: () => void;
    centerImage: () => void;
    alignImageRight: () => void;
    floatImageLeft: () => void;
    floatImageRight: () => void;
    alignTextLeft: () => void;
    alignTextCenter: () => void;
    alignTextRight: () => void;
    openLinkModal: () => void;
    openImageModal: () => void;
    openGalleryModal: () => void;
    toggleHtml: () => void;
    openFileManager: () => void;
    openFormModal?: () => void;
    isRawHtmlMode?: boolean;
}>();
</script>
