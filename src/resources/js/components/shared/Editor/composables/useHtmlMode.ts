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
            // Открываем HTML режим - берем текущий контент
            const currentHtml = editor.value?.getHTML() || '';
            htmlContent.value = currentHtml;
            showHtml.value = true;
        } else {
            // Закрываем без сохранения
            showHtml.value = false;
        }
    };

    const applyHtml = () => {
        if (!editor.value) {
            showHtml.value = false;
            return;
        }

        try {
            const content = htmlContent.value || '<p></p>';

            // === ГЛАВНОЕ: СОХРАНЯЕМ HTML КАК ЕСТЬ ===
            // Обновляем модель напрямую, минуя Tiptap
            emit('update:modelValue', content);

            // Пытаемся обновить редактор, но если не получается — не страшно
            try {
                editor.value.commands.setContent(content);
            } catch (e) {
                // Данные уже сохранены через emit
                console.warn('Tiptap не применил HTML, но данные сохранены');
            }

            showHtml.value = false;

        } catch (error) {
            console.error('Ошибка при применении HTML:', error);
            alert('Ошибка при применении HTML');
        }
    };

    const cancelHtml = () => {
        showHtml.value = false;
    };

    return { toggleHtml, applyHtml, cancelHtml };
}
