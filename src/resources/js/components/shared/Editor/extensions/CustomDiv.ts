import { Node } from '@tiptap/core';

export const CustomDiv = Node.create({
    name: 'div',
    group: 'block',
    content: 'block+',
    draggable: true,

    addAttributes() {
        return {
            class: {
                default: null,
                parseHTML: element => element.getAttribute('class'),
                renderHTML: attributes => {
                    if (!attributes.class) return {};
                    return { class: attributes.class };
                },
            },
            style: {
                default: null,
                parseHTML: element => element.getAttribute('style'),
                renderHTML: attributes => {
                    if (!attributes.style) return {};
                    return { style: attributes.style };
                },
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'div',
                getAttrs: (element) => {
                    const el = element as HTMLElement;
                    return {
                        class: el.getAttribute('class') || '',
                        style: el.getAttribute('style') || '',
                    };
                },
            },
        ];
    },

    renderHTML({  HTMLAttributes }) {
        return ['div', HTMLAttributes, 0];
    },
});
