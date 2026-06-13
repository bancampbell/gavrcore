import { type Ref } from 'vue';
import type { Editor } from '@tiptap/core';

export function useHtmlMode(
    editor: Ref<Editor | null>,
    showHtml: Ref<boolean>,
    htmlContent: Ref<string>,
    emit: (e: 'update:modelValue', value: string) => void
) {
    const toggleHtml = () => {
        if (!showHtml.value) {
            // Загружаем текущий HTML из редактора
            const currentHtml = editor.value?.getHTML();
            htmlContent.value = currentHtml || '';
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
