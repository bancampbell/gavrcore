import { describe, it, expect, afterEach } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Iframe } from '../../../../../modules/MaterialManager/ui/editor/extensions/Iframe';

describe('Iframe extension: контент редактора не теряет iframe (save-path)', () => {
    let editor: Editor | null = null;

    afterEach(() => {
        editor?.destroy();
        editor = null;
    });

    it('iframe Яндекс.Карт переживает инициализацию редактора и сериализацию getHTML', () => {
        editor = new Editor({
            element: document.createElement('div'),
            extensions: [StarterKit.configure({ link: false, underline: false, strike: false }), Iframe],
            content: '<p>Карта проезда:</p><iframe src="https://yandex.ru/map-widget/v1/?um=constructor%3Aabc123&source=constructor" width="570" height="380" frameborder="0"></iframe>',
        });

        const html = editor.getHTML();

        expect(html).toContain('<p>Карта проезда:</p>');
        expect(html).toContain('<iframe');
        expect(html).toMatch(/src="https:\/\/yandex\.ru\/map-widget\/v1\/\?um=constructor%3Aabc123(?:&amp;|&)source=constructor"/);
        expect(html).toContain('width="570"');
        expect(html).toContain('height="380"');
        expect(html).toContain('frameborder="0"');
    });

    it('setContent (HTML-режим редактора) сохраняет iframe в документе', () => {
        editor = new Editor({
            element: document.createElement('div'),
            extensions: [StarterKit.configure({ link: false, underline: false, strike: false }), Iframe],
            content: '<p></p>',
        });

        editor.commands.setContent('<iframe src="https://yandex.ru/map-widget/v1/" width="570" height="380" frameborder="0"></iframe>');

        const html = editor.getHTML();
        expect(html).toContain('<iframe');
        expect(html).toContain('src="https://yandex.ru/map-widget/v1/"');
        expect(html).toContain('width="570"');
        expect(html).toContain('height="380"');
        expect(html).toContain('frameborder="0"');
    });

    it('iframe без src не превращается в узел (parseHTML требует iframe[src])', () => {
        editor = new Editor({
            element: document.createElement('div'),
            extensions: [StarterKit.configure({ link: false, underline: false, strike: false }), Iframe],
            content: '<iframe width="570" height="380"></iframe>',
        });

        expect(editor.getHTML()).not.toContain('<iframe');
    });
});
