import { Extension } from '@tiptap/core';

export const PreserveAttributes = Extension.create({
    name: 'preserveAttributes',
    addGlobalAttributes() {
        return [
            {
                types: ['paragraph', 'heading', 'blockquote', 'codeBlock', 'listItem', 'orderedList', 'bulletList', 'div'],
                attributes: {
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
                },
            },
        ];
    },
});
