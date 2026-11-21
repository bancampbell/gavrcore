import { Node, mergeAttributes } from '@tiptap/core';

// Узел <iframe> для встраивания карт/видео. Без него ProseMirror выбрасывает iframe
// при парсинге HTML редактора (вставка, HTML-режим, загрузка сохранённого контента).
// Безопасность src (белый список хостов, только https) обеспечивается на входе
// (sanitizeHtml при insertContent) и на рендере (ShortcodeRenderer) — не здесь.
export const Iframe = Node.create({
    name: 'iframe',

    group: 'block',

    atom: true,

    addAttributes() {
        return {
            src: { default: null },
            width: { default: null },
            height: { default: null },
            frameborder: { default: null },
            allowfullscreen: { default: null },
            loading: { default: null },
            referrerpolicy: { default: null },
        };
    },

    parseHTML() {
        return [{ tag: 'iframe[src]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['iframe', mergeAttributes(HTMLAttributes)];
    },
});
