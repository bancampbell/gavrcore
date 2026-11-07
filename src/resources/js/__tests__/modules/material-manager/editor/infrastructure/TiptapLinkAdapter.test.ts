import { describe, it, expect, vi, beforeEach } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import { TiptapLinkAdapter } from '../../../../../modules/MaterialManager/infrastructure/adapters/TiptapLinkAdapter';
import { TiptapEditorAdapter } from '../../../../../modules/MaterialManager/infrastructure/adapters/TiptapEditorAdapter';
import { LinkData } from '../../../../../modules/MaterialManager/domain/values/LinkData';

describe('TiptapLinkAdapter', () => {
    let linkAdapter: TiptapLinkAdapter;
    let editorAdapter: TiptapEditorAdapter;
    let editor: Editor;

    beforeEach(() => {
        const element = document.createElement('div');
        linkAdapter = new TiptapLinkAdapter();
        editorAdapter = new TiptapEditorAdapter();

        editor = new Editor({
            element,
            extensions: [
                StarterKit.configure({ link: false }),
                Link.configure({ openOnClick: false, HTMLAttributes: { class: 'text-blue-600 underline' } }),
            ],
            content: '<p>Hello world</p>',
        });

        linkAdapter.init(editor);
        editorAdapter.init(editor);
    });

    it('insertLink adds link to content', () => {
        const linkData = LinkData.create({
            url: 'https://example.com',
            text: 'Example',
        });

        linkAdapter.insertLink(linkData, { from: 0, to: 5 });
        const html = editorAdapter.getHTML();
        expect(html).toContain('href="https://example.com"');
        expect(html).toContain('Hell</a>');
    });

    it('insertLink with target blank', () => {
        const linkData = LinkData.create({
            url: 'https://example.com',
            text: 'Link',
            target: '_blank',
        });

        linkAdapter.insertLink(linkData, { from: 0, to: 5 });
        const html = editorAdapter.getHTML();
        expect(html).toContain('target="_blank"');
    });

    it('updateLink changes link url', () => {
        const linkData = LinkData.create({
            url: 'https://old.com',
            text: 'Old',
        });

        linkAdapter.insertLink(linkData, { from: 0, to: 5 });

        const newLinkData = LinkData.create({
            url: 'https://new.com',
            text: 'New',
            _pos: linkAdapter.getLinkAt(1)?._pos ?? null,
        });

        linkAdapter.updateLink('Old', newLinkData);
        const html = editorAdapter.getHTML();
        expect(html).toContain('href="https://new.com"');
        expect(html).toContain('New');
    });

    it('updateLink по _pos: 3-я из трёх смежных ссылок с похожими якорями меняется, соседи — нет', () => {
        // контент из реального кейса: три плотно стоящие ссылки с почти
        // одинаковыми текстами якорей
        editor.commands.setContent(
            '<p>' +
            '<a href="/c1">Предложения о размере цен (тарифов)...корректировка 2</a>' +
            '<a href="/c2">Предложение о размере цен...</a>' +
            '<a href="/c3">Предложения о размере цен...корректировка</a>' +
            '</p>',
        );

        // позиция внутри ТРЕТЬЕЙ ссылки
        const docText = editor.state.doc.textContent;
        const thirdStart = docText.indexOf('Предложения о размере цен...корректировка', docText.indexOf('Предложение о размере цен...'));
        const retrieved = linkAdapter.getLinkAt(thirdStart + 3);
        expect(retrieved?.url).toBe('/c3');
        expect(retrieved?.text).toBe('Предложения о размере цен...корректировка');
        expect(retrieved?._pos).not.toBeNull();

        linkAdapter.updateLink('Предложения о размере цен...корректировка', LinkData.create({
            url: '/c3-new',
            text: 'Предложения о размере цен...корректировка',
            _pos: retrieved?._pos ?? null,
        }));

        const html = editorAdapter.getHTML();
        // третья ссылка обновлена, соседи нетронуты
        expect(html).toContain('href="/c3-new"');
        expect(html).toContain('href="/c1"');
        expect(html).toContain('href="/c2"');
        expect(html).not.toContain('href="/c3"');
        // дублей якорей нет: всё ещё три ссылки
        expect((html.match(/<a /g) || []).length).toBe(3);

        // повторное чтение третьей ссылки по позиции — новый URL
        const after = linkAdapter.getLinkAt(editor.state.doc.textContent.indexOf('Предложения о размере цен...корректировка') + 3);
        expect(after?.url).toBe('/c3-new');
    });

    it('updateLink без _pos: документ не меняется, только предупреждение', () => {
        editor.commands.setContent(
            '<p>' +
            '<a href="/c1">Предложения о размере цен (тарифов)...корректировка 2</a>' +
            '<a href="/c2">Предложение о размере цен...</a>' +
            '<a href="/c3">Предложения о размере цен...корректировка</a>' +
            '</p>',
        );
        const before = editorAdapter.getHTML();
        const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

        linkAdapter.updateLink('Предложения о размере цен...корректировка', LinkData.create({
            url: '/should-not-appear',
            text: 'X',
        }));

        expect(editorAdapter.getHTML()).toBe(before);
        expect(warn).toHaveBeenCalledOnce();
        expect(editorAdapter.getHTML()).not.toContain('should-not-appear');

        warn.mockRestore();
    });

    it('updateLink с протухшей позицией (документ сократился): документ не меняется', () => {
        editor.commands.setContent('<p><a href="/a">Первое</a><a href="/b">Второе</a></p>');
        const retrieved = linkAdapter.getLinkAt(2);
        expect(retrieved?.url).toBe('/a');

        // документ заменён — старая позиция указывает мимо
        editor.commands.setContent('<p>коротко</p>');

        const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});
        const before = editorAdapter.getHTML();
        linkAdapter.updateLink('Первое', LinkData.create({
            url: '/should-not-appear',
            text: 'Первое',
            _pos: retrieved?._pos ?? null,
        }));

        expect(editorAdapter.getHTML()).toBe(before);
        expect(warn).toHaveBeenCalledOnce();
        warn.mockRestore();
    });

    it('escapes quotes in url and title to prevent attribute breakout (XSS)', () => {
        const linkData = LinkData.create({
            url: 'https://example.com/?q="onmouseover="alert(1)',
            text: 'Quoted',
            title: 'Подсказка "с кавычками" onfocus="alert(2)',
        });

        linkAdapter.insertLink(linkData, { from: 0, to: 5 });
        const html = editorAdapter.getHTML();

        // нет сырого выхода из значения атрибута
        expect(html).not.toContain('q="onmouseover="');
        expect(html).not.toContain('" onfocus="alert(2)"');
        expect(html).toContain('&quot;');

        // данные не повреждены экранированием — round-trip через парсер точный
        const retrieved = linkAdapter.getLinkAt(1);
        expect(retrieved?.url).toBe('https://example.com/?q="onmouseover="alert(1)');
        expect(retrieved?.title).toBe('Подсказка "с кавычками" onfocus="alert(2)');
    });

    it('getLinkAt returns link data', () => {
        const linkData = LinkData.create({
            url: 'https://example.com',
            text: 'Example',
            target: '_blank',
            title: 'Test title',
        });

        linkAdapter.insertLink(linkData, { from: 0, to: 5 });

        const retrieved = linkAdapter.getLinkAt(1);
        expect(retrieved).not.toBeNull();
        expect(retrieved?.url).toBe('https://example.com');
    });

    it('getLinkAt возвращает текст ВСЕГО якоря (mark-диапазон), а не кусок узла', () => {
        // якорь из трёх узлов: «Первое » + «второе»(bold) + « третье»
        editor.commands.setContent('<p>до <a href="/a">Первое <strong>второе</strong> третье</a> после</p>');

        // позиция внутри среднего слова — старый код вернул бы «орое»
        const retrieved = linkAdapter.getLinkAt(14);
        expect(retrieved?.url).toBe('/a');
        expect(retrieved?.text).toBe('Первое второе третье');

        // позиция в конце якоря: граничная — читается через pos-1
        const atEnd = linkAdapter.getLinkAt(23);
        expect(atEnd?.text).toBe('Первое второе третье');
    });
});
