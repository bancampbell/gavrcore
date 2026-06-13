import { ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { LinkData } from '../types/editor';

interface EditorEmits {
    (e: 'openLinkModal', selectedText?: string): void;
    (e: 'editLink', data: LinkData): void;
}

export function useLinkHandlers(emit: EditorEmits) {
    const selectedLinkData = ref<LinkData | null>(null);

    const getLinkData = (link: HTMLAnchorElement): LinkData => ({
        oldText: link.innerText,
        url: link.getAttribute('href') || '',
        text: link.innerText,
        target: link.getAttribute('target') || '_self',
        title: link.getAttribute('title') || '',
    });

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
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            selectedLinkData.value = null;
        }
    };

    const openLinkModal = (selectedText?: string): void => {
        if (selectedLinkData.value) {
            emit('editLink', selectedLinkData.value);
        } else {
            emit('openLinkModal', selectedText || '');
        }
    };

    const setLinkOnSelection = (
        editor: Editor,
        url: string,
        linkText: string,
        target: string,
        title: string
    ): void => {
        if (!editor) return;

        const linkTarget = target === '_blank' ? '_blank' : '_self';
        const linkTitle = title || '';
        const text = linkText || url;

        const { from, to } = editor.state.selection;
        const hasSelection = from !== to;

        if (hasSelection) {
            const selectedText = editor.state.doc.textBetween(from, to);
            const linkHtml = `<a href="${url}" target="${linkTarget}" title="${linkTitle}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${selectedText}</a>`;
            editor.commands.insertContent(linkHtml);
        } else {
            const linkHtml = `<a href="${url}" target="${linkTarget}" title="${linkTitle}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${text}</a>`;
            editor.commands.insertContent(linkHtml);
        }
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

        const linkTarget = newTarget === '_blank' ? '_blank' : '_self';
        const content = editor.getHTML();

        const oldLinkRegex = new RegExp(`<a[^>]*>${oldText}</a>`, 'g');
        const newLinkHtml = `<a href="${newUrl}" target="${linkTarget}" title="${newTitle}" rel="noopener noreferrer nofollow" class="text-blue-600 underline">${newText}</a>`;
        const newContent = content.replace(oldLinkRegex, newLinkHtml);

        editor.commands.setContent(newContent);
    };

    const clearSelectedLink = (): void => {
        document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
        selectedLinkData.value = null;
    };

    return {
        selectedLinkData,
        handleLinkMouseDown,
        openLinkModal,
        setLinkOnSelection,
        updateExistingLink,
        clearSelectedLink,
    };
}
