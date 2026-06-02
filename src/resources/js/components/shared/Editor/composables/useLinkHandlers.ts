import { ref } from 'vue';
import type { Editor } from '@tiptap/core';
import type { LinkData } from '../types/editor';

export function useLinkHandlers(emit: any) {
    const selectedLinkData = ref<LinkData | null>(null);

    const getLinkData = (link: HTMLAnchorElement): LinkData => ({
        oldText: link.innerText,
        url: link.getAttribute('href') || '',
        text: link.innerText,
        target: link.getAttribute('target') || '',
        title: link.getAttribute('title') || '',
    });

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
        } else {
            document.querySelectorAll('.tiptap a').forEach(a => a.classList.remove('selected-link'));
            selectedLinkData.value = null;
        }
    };

    const openLinkModal = () => {
        if (selectedLinkData.value) {
            emit('editLink', selectedLinkData.value);
        } else {
            emit('openLinkModal');
        }
    };

    const setLinkOnSelection = (
        editor: Editor,
        url: string,
        linkText: string,
        target: string,
        title: string
    ) => {
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

    return {
        selectedLinkData,
        handleLinkMouseDown,
        openLinkModal,
        setLinkOnSelection,
    };
}
