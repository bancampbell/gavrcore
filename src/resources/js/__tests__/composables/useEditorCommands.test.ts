import { describe, it, expect, vi, beforeEach } from 'vitest';
import { ref } from 'vue';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import { useEditorCommands } from '@/composables/useEditorCommands';
import { TiptapEditorAdapter } from '@/infrastructure/adapters/TiptapEditorAdapter';
import { TiptapImageAdapter } from '@/infrastructure/adapters/TiptapImageAdapter';
import { ResizableImage } from '@/components/shared/Editor/extensions/ResizableImage';
import { LinkData } from '@/domain/values/LinkData';

describe('useEditorCommands', () => {
    let editor: Editor;
    let editorAdapter: TiptapEditorAdapter;
    let imageAdapter: TiptapImageAdapter;

    const createCommands = (emit = vi.fn(), insertImage = vi.fn()) =>
        useEditorCommands(editorAdapter, ref(''), ref(''), () => {}, insertImage, emit);

    beforeEach(() => {
        document.body.innerHTML = '';
        editorAdapter = new TiptapEditorAdapter();
        imageAdapter = new TiptapImageAdapter();

        editor = new Editor({
            element: document.createElement('div'),
            extensions: [
                StarterKit.configure({ link: false }),
                ResizableImage,
                Link.configure({ openOnClick: false }),
            ],
            content: '<p><a href="https://example.com" target="_blank" title="Подсказка">текст ссылки</a></p>',
        });

        editorAdapter.init(editor);
        imageAdapter.init(editor);
    });

    describe('handleOpenLinkModal', () => {
        it('с выделенной ссылкой эмитит editLink с href/text/target/title из узла', () => {
            const emit = vi.fn();
            const commands = createCommands(emit);
            const selectedLink = ref(LinkData.create({
                url: 'https://example.com',
                text: 'текст ссылки',
                target: '_blank',
                title: 'Подсказка',
                oldText: 'текст ссылки',
            }));

            commands.handleOpenLinkModal(selectedLink);

            expect(emit).toHaveBeenCalledWith('editLink', {
                oldText: 'текст ссылки',
                url: 'https://example.com',
                text: 'текст ссылки',
                target: '_blank',
                title: 'Подсказка',
            });
        });

        it('без выделенной ссылки эмитит openLinkModal с текстом выделения', () => {
            const emit = vi.fn();
            const commands = createCommands(emit);

            commands.handleOpenLinkModal(ref(null));

            expect(emit).toHaveBeenCalledWith('openLinkModal', '');
        });
    });

    describe('handleImageInsert', () => {
        it('с _pos обновляет существующий узел и сохраняет обтекание/отступ', () => {
            editor.commands.setContent('<p>а</p><img src="/storage/old.jpg" alt="Стар" /><p>б</p>');
            const pos = imageAdapter.findImagePosition('/storage/old.jpg');
            expect(pos).not.toBe(-1);

            const emit = vi.fn();
            const insertImage = vi.fn();
            const commands = createCommands(emit, insertImage);

            commands.handleImageInsert({
                url: '/storage/new.jpg',
                alt: 'Нов',
                title: '',
                width: '400',
                height: '',
                align: '',
                float: 'left',
                margin: '10',
                _pos: pos,
            });

            const html = editorAdapter.getHTML();
            expect(html).toContain('src="/storage/new.jpg"');
            expect(html).toContain('alt="Нов"');
            expect(html).toContain('width="400"');
            expect(html).toContain('float: left');
            expect(html).toContain('margin-right: 10px');
            expect((html.match(/<img/g) || []).length).toBe(1);
            expect(insertImage).not.toHaveBeenCalled();
        });

        it('без _pos вставляет новый узел через insertImage use case', () => {
            const emit = vi.fn();
            const insertImage = vi.fn();
            const commands = createCommands(emit, insertImage);

            commands.handleImageInsert({
                url: '/storage/x.jpg',
                alt: '',
                title: '',
                width: '',
                height: '',
                align: 'center',
                float: '',
                margin: '',
            });

            expect(insertImage).toHaveBeenCalledTimes(1);
            const [imageData, position] = insertImage.mock.calls[0];
            expect(imageData.url).toBe('/storage/x.jpg');
            expect(imageData.styleProps.align).toBe('center');
            expect(position).toBeUndefined();
        });
    });
});
