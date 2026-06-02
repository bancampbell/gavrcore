import { type Ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { EditorEmits } from '../types/editor';

export function useHtmlMode(
    editor: Ref<Editor | null>,
    showHtml: Ref<boolean>,
    htmlContent: Ref<string>,
    emit: EditorEmits
) {
    const toggleHtml = () => {
        if (!showHtml.value) {
            htmlContent.value = editor.value?.getHTML() || '';
            showHtml.value = true;
        } else {
            showHtml.value = false;
        }
    };

    const applyHtml = () => {
        if (editor.value) {
            editor.value.commands.setContent(htmlContent.value);
            emit('update:modelValue', htmlContent.value);
        }
        showHtml.value = false;
    };

    const cancelHtml = () => {
        showHtml.value = false;
    };

    return { toggleHtml, applyHtml, cancelHtml };
}
