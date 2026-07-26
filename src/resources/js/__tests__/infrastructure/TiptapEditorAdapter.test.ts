import { describe, it, expect, beforeEach } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import TextAlign from '@tiptap/extension-text-align';
import { TiptapEditorAdapter } from '../../infrastructure/adapters/TiptapEditorAdapter';
import { ResizableImage } from '../../components/shared/Editor/extensions/ResizableImage';
import { CustomDiv } from '../../components/shared/Editor/extensions/CustomDiv';
import { PreserveAttributes } from '../../components/shared/Editor/extensions/PreserveAttributes';

describe('TiptapEditorAdapter', () => {
    let adapter: TiptapEditorAdapter;
    let editor: Editor;

    beforeEach(() => {
        const element = document.createElement('div');
        adapter = new TiptapEditorAdapter();

        editor = new Editor({
            element,
            extensions: [
                StarterKit.configure({ link: false, underline: false, strike: false }),
                PreserveAttributes,
                CustomDiv,
                ResizableImage,
                Link.configure({ openOnClick: false, HTMLAttributes: { class: 'text-blue-600 underline' } }),
                Underline,
                Strike,
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
            ],
            content: '<p>Test content</p>',
        });

        adapter.init(editor);
    });

    it('getHTML returns editor content', () => {
        const html = adapter.getHTML();
        expect(html).toContain('<p>Test content</p>');
    });

    it('setContent replaces content', () => {
        adapter.setContent('<p>New content</p>');
        expect(adapter.getHTML()).toContain('<p>New content</p>');
    });

    it('insertContent inserts at position', () => {
        adapter.insertContent('<strong>Bold</strong>', 0);
        const html = adapter.getHTML();
        expect(html).toContain('<strong>Bold</strong>');
    });

    it('getSelection returns correct selection', () => {
        adapter.setTextSelection(3, 7);
        const selection = adapter.getSelection();
        expect(selection.from).toBe(3);
        expect(selection.to).toBe(7);
    });

    it('getSelection isEmpty when from equals to', () => {
        adapter.setTextSelection(5, 5);
        const selection = adapter.getSelection();
        expect(selection.isEmpty).toBe(true);
    });

    it('getNodeAt returns node at position', () => {
        const node = adapter.getNodeAt(0);
        expect(node).not.toBeNull();
        expect(node?.type).toBe('paragraph');
    });

    it('updateNode changes node attributes', () => {
        adapter.setContent('<p style="text-align: left;">Hello</p>');
        const node = adapter.getNodeAt(0);
        if (node) {
            adapter.updateNode(0, { ...node.attrs, style: 'text-align: center;' });
            const updated = adapter.getNodeAt(0);
            expect(updated?.attrs.style).toContain('text-align: center');
        }
    });

    it('isActive returns true when editor initialized', () => {
        expect(adapter.isActive()).toBe(true);
    });

    it('onUpdate registers callback', () => {
        let updatedHtml = '';
        adapter.onUpdate((html) => { updatedHtml = html; });
        adapter.setContent('<p>Updated</p>');
        adapter.triggerUpdate(adapter.getHTML());
        expect(updatedHtml).toContain('<p>Updated</p>');
    });
});
