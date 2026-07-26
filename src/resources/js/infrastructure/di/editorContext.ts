import { provide, inject, type InjectionKey } from 'vue';
import { TiptapEditorAdapter } from '../adapters/TiptapEditorAdapter';
import { TiptapImageAdapter } from '../adapters/TiptapImageAdapter';
import { TiptapLinkAdapter } from '../adapters/TiptapLinkAdapter';
import { InsertImageUseCase } from '../../application/usecases/InsertImageUseCase';
import { UpdateImageUseCase } from '../../application/usecases/UpdateImageUseCase';
import { InsertLinkUseCase } from '../../application/usecases/InsertLinkUseCase';
import { UpdateLinkUseCase } from '../../application/usecases/UpdateLinkUseCase';
import { DeleteNodeUseCase } from '../../application/usecases/DeleteNodeUseCase';
import { ToggleHtmlModeUseCase } from '../../application/usecases/ToggleHtmlModeUseCase';

export interface EditorContext {
    editorAdapter: TiptapEditorAdapter;
    imageAdapter: TiptapImageAdapter;
    linkAdapter: TiptapLinkAdapter;
    insertImageUseCase: InsertImageUseCase;
    updateImageUseCase: UpdateImageUseCase;
    insertLinkUseCase: InsertLinkUseCase;
    updateLinkUseCase: UpdateLinkUseCase;
    deleteNodeUseCase: DeleteNodeUseCase;
    toggleHtmlModeUseCase: ToggleHtmlModeUseCase;
}

const EDITOR_CONTEXT_KEY: InjectionKey<EditorContext> = Symbol('editorContext');

export function createEditorContext(): EditorContext {
    const editorAdapter = new TiptapEditorAdapter();
    const imageAdapter = new TiptapImageAdapter();
    const linkAdapter = new TiptapLinkAdapter();

    return {
        editorAdapter,
        imageAdapter,
        linkAdapter,
        insertImageUseCase: new InsertImageUseCase(editorAdapter, imageAdapter),
        updateImageUseCase: new UpdateImageUseCase(editorAdapter, imageAdapter),
        insertLinkUseCase: new InsertLinkUseCase(editorAdapter, linkAdapter),
        updateLinkUseCase: new UpdateLinkUseCase(editorAdapter, linkAdapter),
        deleteNodeUseCase: new DeleteNodeUseCase(editorAdapter),
        toggleHtmlModeUseCase: new ToggleHtmlModeUseCase(editorAdapter),
    };
}

export function provideEditorContext(context: EditorContext): void {
    provide(EDITOR_CONTEXT_KEY, context);
}

export function useEditorContext(): EditorContext | null {
    return inject(EDITOR_CONTEXT_KEY, null);
}
