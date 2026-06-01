<template>
    <div class="border border-gray-200 rounded-lg overflow-hidden bg-white flex flex-col h-full">
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

// Кастомное расширение для ресайза изображений
const ResizableImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            width: {
                default: null,
                parseHTML: element => element.getAttribute('width'),
                renderHTML: attributes => {
                    if (!attributes.width) return {};
                    return { width: attributes.width };
                },
            },
            height: {
                default: null,
                parseHTML: element => element.getAttribute('height'),
                renderHTML: attributes => {
                    if (!attributes.height) return {};
                    return { height: attributes.height };
                },
            },
        };
    },

    addNodeView() {
        return ({ editor, node, getPos }) => {
            const container = document.createElement('div');
            container.style.position = 'relative';
            container.style.display = 'inline-block';

            const img = document.createElement('img');
            img.src = node.attrs.src;
            img.alt = node.attrs.alt || '';
            img.title = node.attrs.title || '';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';

            if (node.attrs.width) {
                img.style.width = `${node.attrs.width}px`;
            }
            if (node.attrs.height) {
                img.style.height = `${node.attrs.height}px`;
            }

            const resizeHandle = document.createElement('div');
            resizeHandle.style.position = 'absolute';
            resizeHandle.style.width = '12px';
            resizeHandle.style.height = '12px';
            resizeHandle.style.backgroundColor = '#3b82f6';
            resizeHandle.style.borderRadius = '50%';
            resizeHandle.style.border = '2px solid white';
            resizeHandle.style.cursor = 'nw-resize';
            resizeHandle.style.zIndex = '10';
            resizeHandle.style.opacity = '0';
            resizeHandle.style.transition = 'opacity 0.2s';

            const updateHandlePosition = () => {
                const imgRect = img.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                resizeHandle.style.right = `${containerRect.right - imgRect.right}px`;
                resizeHandle.style.bottom = `${containerRect.bottom - imgRect.bottom}px`;
            };

            container.addEventListener('mouseenter', () => {
                resizeHandle.style.opacity = '1';
                updateHandlePosition();
            });
            container.addEventListener('mouseleave', () => {
                resizeHandle.style.opacity = '0';
            });

            let isResizing = false;
            let startX = 0;
            let startWidth = 0;

            const onMouseMove = (e: MouseEvent) => {
                if (!isResizing) return;
                const dx = e.clientX - startX;
                const newWidth = Math.max(50, startWidth + dx);
                img.style.width = `${newWidth}px`;
                img.style.height = 'auto';

                if (getPos) {
                    editor.commands.updateAttributes(node.type, {
                        width: newWidth,
                        height: null,
                    });
                }
                updateHandlePosition();
            };

            const onMouseUp = () => {
                isResizing = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            };

            resizeHandle.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                isResizing = true;
                startX = e.clientX;
                startWidth = img.offsetWidth;
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            container.appendChild(img);
            container.appendChild(resizeHandle);

            window.addEventListener('resize', updateHandlePosition);

            const observer = new MutationObserver(updateHandlePosition);
            observer.observe(container, { attributes: true, attributeFilter: ['style'] });

            return {
                dom: container,
                update: (updatedNode) => {
                    if (updatedNode.type !== node.type) return false;
                    img.src = updatedNode.attrs.src;
                    img.alt = updatedNode.attrs.alt || '';
                    img.title = updatedNode.attrs.title || '';
                    if (updatedNode.attrs.width) {
                        img.style.width = `${updatedNode.attrs.width}px`;
                    }
                    img.style.height = 'auto';
                    updateHandlePosition();
                    return true;
                },
                destroy: () => {
                    window.removeEventListener('resize', updateHandlePosition);
                    observer.disconnect();
                },
            };
        };
    },
});

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'openLinkModal'): void;
    (e: 'openImageManager', imageData?: { url: string; alt: string; title: string; width?: string; height?: string; align?: string }): void;
    (e: 'editLink', data: { oldText: string; url: string; text: string; target: string; title: string }): void;
}>();

const editorElement = ref<HTMLElement>();
const showHtml = ref(false);
const htmlContent = ref('');
let editor: Editor | null = null;
let selectedImageData: { url: string; alt: string; title: string; width?: string; height?: string; align?: string } | null = null;
const selectedLinkData = ref<{ oldText: string; url: string; text: string; target: string; title: string } | null>(null);
const selectedImageAlign = ref<string>('');

const setLinkOnSelection = (url: string, linkText: string, target: string, title: string) => {
    if (!editor) return;

    const { from, to } = editor.state.selection;
    const hasSelection = from !== to;

    if (hasSelection) {
        const selectedText = editor.state.doc.textBetween(from, to);
        const linkHtml = `<a href="${url}" target="${target}" title="${title}">${selectedText}</a>`;
        editor.commands.insertContent(linkHtml);
    } else {
        const linkHtml = `<a href="${url}" target="${target}" title="${title}">${linkText || url}</a>`;
        editor.commands.insertContent(linkHtml);
    }
};

const insertContent = (html: string) => {
    if (editor) {
        editor.commands.insertContent(html);
        const updatedHtml = editor.getHTML();
        emit('update:modelValue', updatedHtml);
    }
};

const updateImage = (oldUrl: string, newData: { url: string; alt: string; title: string; width?: string; height?: string; align?: string }) => {
    if (!editor) return;

    let style = '';
    if (newData.width && newData.width !== '') style += `width: ${newData.width}px; `;
    if (newData.height && newData.height !== '') style += `height: ${newData.height}px; `;

    const styleAttr = style ? ` style="${style.trim()}"` : '';
    const newImgHtml = `<img src="${newData.url}" alt="${newData.alt}" title="${newData.title}"${styleAttr} />`;

    const currentHtml = editor.getHTML();
    const oldImgRegex = new RegExp(`<img[^>]*src="${oldUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}"[^>]*>`, 'g');
    const updatedHtml = currentHtml.replace(oldImgRegex, newImgHtml);
    editor.commands.setContent(updatedHtml);
    emit('update:modelValue', updatedHtml);
};

const alignImageLeft = () => {
    if (!editor) return;

    const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

    if (selectedImg) {
        selectedImageAlign.value = 'left';
        const container = selectedImg.parentElement;
        if (container) {
            container.style.display = 'block';
            container.style.textAlign = 'left';
        }

        selectedImg.style.display = 'block';
        selectedImg.style.marginLeft = '0';
        selectedImg.style.marginRight = 'auto';

        const { state } = editor;
        const { tr } = state;

        state.doc.descendants((node, pos) => {
            if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                tr.setNodeMarkup(pos, undefined, {
                    ...node.attrs,
                    style: `display: block; margin-left: 0; margin-right: auto; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                });
            }
        });

        editor.view.dispatch(tr);
    } else {
        selectedImageAlign.value = '';
        editor.chain().focus().setTextAlign('left').run();
    }
};

const centerImage = () => {
    if (!editor) return;

    const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

    if (selectedImg) {
        selectedImageAlign.value = 'center';
        const container = selectedImg.parentElement;
        if (container) {
            container.style.display = 'block';
            container.style.textAlign = 'center';
        }

        selectedImg.style.display = 'block';
        selectedImg.style.marginLeft = 'auto';
        selectedImg.style.marginRight = 'auto';

        const { state } = editor;
        const { tr } = state;

        state.doc.descendants((node, pos) => {
            if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                tr.setNodeMarkup(pos, undefined, {
                    ...node.attrs,
                    style: `display: block; margin-left: auto; margin-right: auto; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                });
            }
        });

        editor.view.dispatch(tr);
    } else {
        selectedImageAlign.value = '';
        editor.chain().focus().setTextAlign('center').run();
    }
};

const alignImageRight = () => {
    if (!editor) return;

    const selectedImg = document.querySelector('.tiptap img.selected-image') as HTMLImageElement;

    if (selectedImg) {
        selectedImageAlign.value = 'right';
        const container = selectedImg.parentElement;
        if (container) {
            container.style.display = 'block';
            container.style.textAlign = 'right';
        }

        selectedImg.style.display = 'block';
        selectedImg.style.marginLeft = 'auto';
        selectedImg.style.marginRight = '0';

        const { state } = editor;
        const { tr } = state;

        state.doc.descendants((node, pos) => {
            if (node.type.name === 'image' && node.attrs.src === selectedImg.src) {
                tr.setNodeMarkup(pos, undefined, {
                    ...node.attrs,
                    style: `display: block; margin-left: auto; margin-right: 0; width: ${selectedImg.style.width || 'auto'}; height: auto;`,
                });
            }
        });

        editor.view.dispatch(tr);
    } else {
        selectedImageAlign.value = '';
        editor.chain().focus().setTextAlign('right').run();
    }
};

const openLinkModal = () => {
    if (selectedLinkData.value) {
        emit('editLink', selectedLinkData.value);
    } else {
        emit('openLinkModal');
    }
};

const openImageModal = () => {
    if (selectedImageData) {
        emit('openImageManager', selectedImageData);
    } else {
        emit('openImageManager');
    }
};

const openFileManager = () => {
    console.log('Открыть файловый менеджер');
    alert('Файловый менеджер будет здесь');
};

defineExpose({
    setLinkOnSelection,
    insertContent,
    updateImage
});

const getImageData = (img: HTMLImageElement) => {
    let align = '';
    const style = img.getAttribute('style') || '';
    if (style.includes('margin-left: auto') && style.includes('margin-right: auto')) {
        align = 'center';
    } else if (style.includes('margin-left: 0')) {
        align = 'left';
    } else if (style.includes('margin-right: 0')) {
        align = 'right';
    }
    selectedImageAlign.value = align;

    let width = img.getAttribute('width') || '';
    let height = img.getAttribute('height') || '';
    if (!width && style) {
        const widthMatch = style.match(/width:\s*(\d+)px/);
        if (widthMatch) width = widthMatch[1];
    }
    if (!height && style) {
        const heightMatch = style.match(/height:\s*(\d+)px/);
        if (heightMatch) height = heightMatch[1];
    }

    return {
        url: img.getAttribute('src') || '',
        alt: img.getAttribute('alt') || '',
        title: img.getAttribute('title') || '',
        width: width,
        height: height,
        align: align
    };
};

const getLinkData = (link: HTMLAnchorElement) => {
    return {
        oldText: link.innerText,
        url: link.getAttribute('href') || '',
        text: link.innerText,
        target: link.getAttribute('target') || '',
        title: link.getAttribute('title') || ''
    };
};

const handleImageClick = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    const img = target.closest('img');
    if (img) {
        e.preventDefault();
        e.stopPropagation();
        selectedImageData = getImageData(img);
        document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
        img.classList.add('selected-image');
        if (selectedLinkData.value) {
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            selectedLinkData.value = null;
        }
    } else {
        document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
        selectedImageData = null;
        selectedImageAlign.value = '';
    }
};

const handleLinkMouseDown = (e: MouseEvent) => {
    const target = e.target as HTMLElement;
    const link = target.closest('a');
    if (link) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        selectedLinkData.value = getLinkData(link);
        document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
        link.classList.add('selected-link');
        if (selectedImageData) {
            document.querySelectorAll('.tiptap img').forEach(i => i.classList.remove('selected-image'));
            selectedImageData = null;
            selectedImageAlign.value = '';
        }
    } else {
        document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
        selectedLinkData.value = null;
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
            ResizableImage,
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
        editorContainer.addEventListener('mousedown', handleLinkMouseDown, true);
        editorContainer.addEventListener('click', handleImageClick, true);
    }
});

watch(() => props.modelValue, (newValue) => {
    if (editor && !showHtml.value && newValue !== editor.getHTML()) {
        editor.commands.setContent(newValue || '<p></p>');
    }
});

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
