import { ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { LinkData } from '../types/editor';

interface EditorEmits {
    (e: 'openLinkModal', selectedText?: string): void;
    (e: 'editLink', data: LinkData): void;
}

export function useLinkHandlers(emit: EditorEmits) {
    const selectedLinkData = ref<LinkData | null>(null);
    let savedLinkPosition: { from: number; to: number } | null = null;

    const getLinkData = (link: HTMLAnchorElement): LinkData => ({
        oldText: link.innerText,
        url: link.getAttribute('href') || '',
        text: link.innerText,
        target: link.getAttribute('target') || '_self',
        title: link.getAttribute('title') || '',
    });

    const clearSelection = (): void => {
        document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
        selectedLinkData.value = null;
        savedLinkPosition = null;
    };

    const handleLinkMouseDown = (e: MouseEvent): void => {
        const target = e.target as HTMLElement;
        const link = target.closest('a');

        if (link) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            selectedLinkData.value = getLinkData(link);
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            link.classList.add('selected-link');
        } else {
            clearSelection();
        }
    };

    const openLinkModal = (selectedText?: string): void => {
        if (selectedLinkData.value) {
            emit('editLink', selectedLinkData.value);
        } else {
            emit('openLinkModal', selectedText || '');
        }
    };

    const saveLinkPosition = (editor: Editor, linkText: string): void => {
        const { state } = editor;
        state.doc.descendants((node, pos) => {
            if (node.isText && node.text === linkText) {
                const hasLink = node.marks.some((mark: any) => mark.type.name === 'link');
                if (hasLink) {
                    savedLinkPosition = { from: pos, to: pos + node.text.length };
                    return false;
                }
            }
            return true;
        });
    };

    const buildLinkHtml = (url: string, text: string, target: string, title: string): string => {
        const linkTarget = target === '_blank' ? '_blank' : '_self';
        const linkTitle = title || '';
        return `<a href="${url}" target="${linkTarget}" title="${linkTitle}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${text}</a>`;
    };

    const setLinkOnSelection = (
        editor: Editor,
        url: string,
        linkText: string,
        target: string,
        title: string
    ): void => {
        if (!editor) return;

        const text = linkText || url;
        const { from, to } = editor.state.selection;
        const hasSelection = from !== to;
        const selectedText = hasSelection ? editor.state.doc.textBetween(from, to) : text;
        const linkHtml = buildLinkHtml(url, selectedText, target, title);

        editor.commands.insertContent(linkHtml);
        savedLinkPosition = null;
    };

    const updateExistingLink = (
        editor: Editor,
        oldText: string,
        newUrl: string,
        newText: string,
        newTarget: string,
        newTitle: string
    ): void => {
        if (!editor) return;

        const newTextToInsert = newText || oldText;
        const linkHtml = buildLinkHtml(newUrl, newTextToInsert, newTarget, newTitle);

        const updateLinkAtPosition = (from: number, to: number): void => {
            editor.commands.setTextSelection({ from, to });
            editor.commands.insertContent(linkHtml);

            selectedLinkData.value = {
                oldText: newTextToInsert,
                url: newUrl,
                text: newTextToInsert,
                target: newTarget || '_self',
                title: newTitle || '',
            };

            savedLinkPosition = null;
        };

        // Если есть сохраненная позиция — используем её
        if (savedLinkPosition) {
            updateLinkAtPosition(savedLinkPosition.from, savedLinkPosition.to);
            return;
        }

        // Fallback: ищем ссылку по тексту
        const { state } = editor;
        let found = false;

        state.doc.descendants((node, pos) => {
            if (node.isText && node.text === oldText) {
                const hasLink = node.marks.some((mark: any) => mark.type.name === 'link');
                if (hasLink) {
                    updateLinkAtPosition(pos, pos + oldText.length);
                    found = true;
                    return false;
                }
            }
            return true;
        });

        if (!found) {
            setLinkOnSelection(editor, newUrl, newTextToInsert, newTarget, newTitle);
        }
    };

    return {
        selectedLinkData,
        handleLinkMouseDown,
        openLinkModal,
        setLinkOnSelection,
        updateExistingLink,
        clearSelection,
        saveLinkPosition,
    };
}
