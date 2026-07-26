import { describe, it, expect, beforeEach } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { TiptapImageAdapter } from '../../infrastructure/adapters/TiptapImageAdapter';
import { TiptapEditorAdapter } from '../../infrastructure/adapters/TiptapEditorAdapter';
import { ResizableImage } from '../../components/shared/Editor/extensions/ResizableImage';
import { ImageData } from '../../domain/values/ImageData';

describe('TiptapImageAdapter', () => {
    let imageAdapter: TiptapImageAdapter;
    let editorAdapter: TiptapEditorAdapter;
    let editor: Editor;

    beforeEach(() => {
        const element = document.createElement('div');
        imageAdapter = new TiptapImageAdapter();
        editorAdapter = new TiptapEditorAdapter();

        editor = new Editor({
            element,
            extensions: [
                StarterKit.configure({ link: false }),
                ResizableImage,
            ],
            content: '<p></p>',
        });

        imageAdapter.init(editor);
        editorAdapter.init(editor);
    });

    it('insertImage adds image to content', () => {
        const imageData = ImageData.create({
            url: '/storage/test.jpg',
            alt: 'Test image',
        });

        imageAdapter.insertImage(imageData, 0);
        const html = editorAdapter.getHTML();
        expect(html).toContain('src="/storage/test.jpg"');
        expect(html).toContain('alt="Test image"');
    });

    it('insertImage with width and height', () => {
        const imageData = ImageData.create({
            url: '/storage/test.jpg',
            width: 200,
            height: 150,
        });

        imageAdapter.insertImage(imageData, 0);
        const html = editorAdapter.getHTML();
        expect(html).toContain('width="200"');
        expect(html).toContain('height="150"');
    });

    it('insertImage with alignment', () => {
        const imageData = ImageData.create({
            url: '/storage/test.jpg',
            align: 'center',
        });

        imageAdapter.insertImage(imageData, 0);
        const image = imageAdapter.getImageAt(0);
        expect(image).not.toBeNull();
        expect(image?.styleProps.align).toBe('center');
    });

    it('updateImage changes image attributes', () => {
        const imageData = ImageData.create({
            url: '/storage/old.jpg',
            alt: 'Old',
        });

        imageAdapter.insertImage(imageData, 0);

        const newImageData = ImageData.create({
            url: '/storage/new.jpg',
            alt: 'New',
            width: 300,
        });

        imageAdapter.updateImage(0, newImageData);
        const html = editorAdapter.getHTML();
        expect(html).toContain('src="/storage/new.jpg"');
        expect(html).toContain('alt="New"');
        expect(html).toContain('width="300"');
    });

    it('findImagePosition returns correct position', () => {
        imageAdapter.insertImage(ImageData.create({ url: '/storage/test.jpg' }), 0);
        imageAdapter.insertImage(ImageData.create({ url: '/storage/second.jpg' }), 1);

        const pos = imageAdapter.findImagePosition('/storage/second.jpg');
        expect(pos).toBeGreaterThan(0);
    });

    it('findImagePosition returns -1 for non-existent image', () => {
        const pos = imageAdapter.findImagePosition('/storage/nonexistent.jpg');
        expect(pos).toBe(-1);
    });

    it('getImageAt returns null for non-image node', () => {
        const image = imageAdapter.getImageAt(0);
        expect(image).toBeNull();
    });
});
