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

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
    (e: 'apply'): void;
    (e: 'close'): void;
}>();

const codeEditorRef = ref<HTMLElement>();
let codeEditorView: EditorView | null = null;
let currentValue = ref(props.modelValue || '');
let isApplying = false;

const apply = () => {
    if (isApplying) return;
    isApplying = true;
    emit('update:modelValue', currentValue.value);
    emit('apply');
    setTimeout(() => {
        isApplying = false;
    }, 300);
};

const cancel = () => {
    emit('close');
};

onMounted(async () => {
    await nextTick();
    if (!codeEditorRef.value) return;

    const initialHtml = props.modelValue || '';

    currentValue.value = initialHtml;

    codeEditorView = new EditorView({
        doc: initialHtml,
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
});

onBeforeUnmount(() => {
    if (codeEditorView) {
        codeEditorView.destroy();
        codeEditorView = null;
    }
});
</script>

<style scoped>
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
