<template>
    <div class="flex flex-col h-full border border-gray-300 rounded-lg overflow-hidden">
        <div class="bg-gray-100 px-4 py-2 border-b flex justify-between items-center flex-shrink-0">
            <span class="text-sm font-medium text-gray-700">Режим HTML</span>
            <div class="flex gap-2">
                <button @click="cancel" class="px-3 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300 transition">
                    Отмена
                </button>
                <button @click="apply" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition">
                    Обновить
                </button>
            </div>
        </div>
        <div ref="codeEditorRef" class="flex-1 overflow-auto"></div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { EditorView, basicSetup } from 'codemirror';
import { html } from '@codemirror/lang-html';
import { EditorView as EditorViewExt } from '@codemirror/view';

// Prettier
import { format } from 'prettier/standalone';
import parserHtml from 'prettier/plugins/html';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'close'): void;
}>();

const codeEditorRef = ref<HTMLElement>();
let codeEditorView: EditorView | null = null;
let currentValue = ref(props.modelValue || '');
let isApplying = false;

const apply = () => {
    if (isApplying) return;
    isApplying = true;
    console.log('[RawHtmlEditor] apply clicked, value length:', currentValue.value?.length || 0);
    emit('update:modelValue', currentValue.value);
    emit('close');
    setTimeout(() => {
        isApplying = false;
    }, 300);
};

const cancel = () => {
    console.log('[RawHtmlEditor] cancel clicked');
    emit('close');
};

onMounted(async () => {
    console.log('[RawHtmlEditor] ===== MOUNTED =====');
    console.log('[RawHtmlEditor] props.modelValue length:', props.modelValue?.length || 0);
    console.log('[RawHtmlEditor] props.modelValue first 100 chars:', props.modelValue?.substring(0, 100) || 'empty');

    await nextTick();
    if (!codeEditorRef.value) return;

    let formattedHtml = props.modelValue || '';
    console.log('[RawHtmlEditor] formattedHtml length:', formattedHtml.length);

    try {
        formattedHtml = await format(formattedHtml, {
            parser: 'html',
            plugins: [parserHtml],
            printWidth: 80,
            tabWidth: 2,
            useTabs: false,
        });
    } catch (e) {
        console.warn('HTML formatting failed:', e);
    }
    currentValue.value = formattedHtml;
    console.log('[RawHtmlEditor] final currentValue length:', currentValue.value?.length || 0);

    codeEditorView = new EditorView({
        doc: formattedHtml,
        extensions: [
            basicSetup,
            html(),
            EditorViewExt.lineWrapping,
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    currentValue.value = update.state.doc.toString();
                }
            }),
        ],
        parent: codeEditorRef.value,
    });

    codeEditorView.focus();
    console.log('[RawHtmlEditor] ===== MOUNTED END =====');
});

onBeforeUnmount(() => {
    if (codeEditorView) {
        codeEditorView.destroy();
        codeEditorView = null;
    }
});
</script>

<style scoped>
/* Стили для CodeMirror */
:deep(.cm-editor) {
    height: 100% !important;
    min-height: 500px !important;
}

:deep(.cm-scroller) {
    overflow: auto !important;
    max-height: 100% !important;
}

:deep(.cm-content) {
    padding: 12px !important;
    font-family: 'JetBrains Mono', 'Fira Code', monospace !important;
    font-size: 13px !important;
    line-height: 1.6 !important;
    min-height: 100% !important;
}
</style>
