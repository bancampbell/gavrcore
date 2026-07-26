import { Extension } from '@tiptap/core';

function cleanHTML(html: string): string {
    return html
        .replace(/style="[^"]*"/g, '')
        .replace(/mso-[^=]+="[^"]*"/g, '')
        .replace(/mso-[^=]+=[^ >]+/g, '')
        .replace(/class="[^"]*"/g, '')
        .replace(/<p>\s*<\/p>/g, '')
        .replace(/<span[^>]*>/g, '')
        .replace(/<\/span>/g, '')
        .replace(/<font[^>]*>/g, '')
        .replace(/<\/font>/g, '')
        .replace(/<(strong|em|b|i|u|s)>\s*<\/(strong|em|b|i|u|s)>/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}

export const CleanPaste = Extension.create({
    name: 'cleanPaste',

    addOptions() {
        return {
            cleanHTML: cleanHTML,
        };
    },
});
