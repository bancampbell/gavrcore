import { describe, it, expect, beforeEach } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import { TiptapLinkAdapter } from '../../infrastructure/adapters/TiptapLinkAdapter';
import { TiptapEditorAdapter } from '../../infrastructure/adapters/TiptapEditorAdapter';
import { LinkData } from '../../domain/values/LinkData';

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
        });

        linkAdapter.updateLink('Old', newLinkData);
        const html = editorAdapter.getHTML();
        expect(html).toContain('href="https://new.com"');
        expect(html).toContain('New');
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
});
