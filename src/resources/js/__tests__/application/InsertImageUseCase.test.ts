import { describe, it, expect, vi, beforeEach } from 'vitest';
import { InsertImageUseCase } from '../../application/usecases/InsertImageUseCase';
import type { EditorPort } from '../../domain/ports/EditorPort';
import type { ImagePort } from '../../domain/ports/ImagePort';
import { ImageData } from '../../domain/values/ImageData';
import { Selection } from '../../domain/values/Selection';

describe('InsertImageUseCase', () => {
    let editorPort: EditorPort;
    let imagePort: ImagePort;
    let useCase: InsertImageUseCase;

    beforeEach(() => {
        editorPort = {
            getHTML: vi.fn(),
            setContent: vi.fn(),
            insertContent: vi.fn(),
            getSelection: vi.fn().mockReturnValue(new Selection(10, 10, '')),
            setTextSelection: vi.fn(),
            getNodeAt: vi.fn(),
            updateNode: vi.fn(),
            isActive: vi.fn().mockReturnValue(true),
            onUpdate: vi.fn(),
            destroy: vi.fn(),
        };

        imagePort = {
            insertImage: vi.fn(),
            updateImage: vi.fn(),
            getImageAt: vi.fn(),
            findImagePosition: vi.fn(),
        };

        useCase = new InsertImageUseCase(editorPort, imagePort);
    });

    it('inserts image at current selection position', () => {
        const imageData = ImageData.create({ url: '/storage/test.jpg', alt: 'Test' });
        useCase.execute(imageData);
        expect(imagePort.insertImage).toHaveBeenCalledWith(imageData, 10);
    });

    it('inserts image at specified position', () => {
        const imageData = ImageData.create({ url: '/storage/test.jpg' });
        useCase.execute(imageData, 25);
        expect(imagePort.insertImage).toHaveBeenCalledWith(imageData, 25);
    });

    it('does nothing when editor is not active', () => {
        editorPort.isActive = vi.fn().mockReturnValue(false);
        const imageData = ImageData.create({ url: '/storage/test.jpg' });
        useCase.execute(imageData);
        expect(imagePort.insertImage).not.toHaveBeenCalled();
    });

    it('inserts image with all style properties', () => {
        const imageData = ImageData.create({
            url: '/storage/test.jpg',
            alt: 'Alt',
            title: 'Title',
            width: 200,
            height: 150,
            align: 'center',
        });
        useCase.execute(imageData);
        expect(imagePort.insertImage).toHaveBeenCalledWith(imageData, 10);
    });
});
