import { describe, it, expect, vi, beforeEach } from 'vitest';
import { InsertLinkUseCase } from '../../application/usecases/InsertLinkUseCase';
import type { EditorPort } from '../../domain/ports/EditorPort';
import type { LinkPort } from '../../domain/ports/LinkPort';
import { LinkData } from '../../domain/values/LinkData';
import { Selection } from '../../domain/values/Selection';

describe('InsertLinkUseCase', () => {
    let editorPort: EditorPort;
    let linkPort: LinkPort;
    let useCase: InsertLinkUseCase;

    beforeEach(() => {
        editorPort = {
            getHTML: vi.fn(),
            setContent: vi.fn(),
            insertContent: vi.fn(),
            getSelection: vi.fn().mockReturnValue(new Selection(5, 15, 'selected text')),
            setTextSelection: vi.fn(),
            getNodeAt: vi.fn(),
            updateNode: vi.fn(),
            isActive: vi.fn().mockReturnValue(true),
            onUpdate: vi.fn(),
            destroy: vi.fn(),
        };

        linkPort = {
            insertLink: vi.fn(),
            updateLink: vi.fn(),
            getLinkAt: vi.fn(),
        };

        useCase = new InsertLinkUseCase(editorPort, linkPort);
    });

    it('inserts link at current selection', () => {
        const linkData = LinkData.create({ url: 'https://example.com', text: 'Example' });
        useCase.execute(linkData);
        expect(linkPort.insertLink).toHaveBeenCalledWith(linkData, { from: 5, to: 15 });
    });

    it('does nothing when editor is not active', () => {
        editorPort.isActive = vi.fn().mockReturnValue(false);
        const linkData = LinkData.create({ url: 'https://example.com' });
        useCase.execute(linkData);
        expect(linkPort.insertLink).not.toHaveBeenCalled();
    });
});
