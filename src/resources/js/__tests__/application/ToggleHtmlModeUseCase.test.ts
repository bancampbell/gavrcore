import { describe, it, expect, beforeEach, vi } from 'vitest';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Strike from '@tiptap/extension-strike';
import TextAlign from '@tiptap/extension-text-align';
import { TableKit } from '@tiptap/extension-table';
import { ToggleHtmlModeUseCase } from '../../modules/MaterialManager/application/usecases/ToggleHtmlModeUseCase';
import { TiptapEditorAdapter } from '../../modules/MaterialManager/infrastructure/adapters/TiptapEditorAdapter';
import { ResizableImage } from '../../components/shared/Editor/extensions/ResizableImage';
import { CustomDiv } from '../../components/shared/Editor/extensions/CustomDiv';
import { PreserveAttributes } from '../../components/shared/Editor/extensions/PreserveAttributes';

// Регексы копируют парсер шорткодов из ShortcodeRenderer.vue
const GALLERY_SHORTCODE_RE = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g;
const FORM_SHORTCODE_RE = /\[form\s+id="(\d+)"(?:\s+([^\]]*))?\]/g;

const SAMPLE_HTML =
    '<h2>Заголовок материала</h2>' +
    '<ol>' +
    '<li><p>Текст <a href="/a">one</a> и <a href="/b" target="_blank">two</a>.</p></li>' +
    '<li><p>Второй пункт</p></li>' +
    '</ol>' +
    '<p>[gallery id="1" name="1"]</p>' +
    '<p><img src="/storage/pic.jpg" alt="Картинка" width="400" height="300"></p>';

function parseNormalized(html: string): HTMLElement {
    const container = document.createElement('div');
    container.innerHTML = html;

    const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT);
    const toRemove: Node[] = [];
    let node = walker.nextNode();
    while (node) {
        if (!/\S/.test(node.textContent ?? '')) {
            toRemove.push(node);
        }
        node = walker.nextNode();
    }
    toRemove.forEach((textNode) => textNode.parentNode?.removeChild(textNode));

    return container;
}

// Пустые <p> без контента. setContent сам добавляет такой в конец документа,
// заканчивающегося картинкой (проверено на немногоформатированном HTML — это
// поведение редактора, а не форматтера), а hoist картинки оставляет такой перед <img>.
function parseNormalizedIgnoringEmptyParagraphs(html: string): HTMLElement {
    const container = parseNormalized(html);
    container.querySelectorAll('p:empty').forEach((p) => p.remove());
    return container;
}

function indentOfLineContaining(text: string, lines: string[]): number {
    const line = lines.find((l) => l.includes(text));
    expect(line, `строка с "${text}"`).toBeDefined();
    return line!.length - line!.trimStart().length;
}

function indentOfLineMatching(pattern: RegExp, lines: string[]): number {
    const line = lines.find((l) => pattern.test(l.trim()));
    expect(line, `строка по ${pattern}`).toBeDefined();
    return line!.length - line!.trimStart().length;
}

describe('ToggleHtmlModeUseCase', () => {
    let adapter: TiptapEditorAdapter;
    let useCase: ToggleHtmlModeUseCase;
    let editor: Editor;

    beforeEach(() => {
        adapter = new TiptapEditorAdapter();
        useCase = new ToggleHtmlModeUseCase(adapter);

        editor = new Editor({
            element: document.createElement('div'),
            extensions: [
                StarterKit.configure({ link: false, underline: false, strike: false }),
                PreserveAttributes,
                CustomDiv,
                ResizableImage,
                Link.configure({ openOnClick: false, HTMLAttributes: { class: 'text-blue-600 underline' } }),
                Underline,
                Strike,
                TextAlign.configure({ types: ['heading', 'paragraph'] }),
                TableKit,
            ],
            content: SAMPLE_HTML,
        });

        adapter.init(editor);
    });

    it('getCurrentHtml возвращает HTML с переносами строк и отступами по вложенности', async () => {
        const formatted = await useCase.getCurrentHtml();
        const lines = formatted.split('\n');

        expect(lines.length).toBeGreaterThan(8);

        // Блочные теги с новой строки
        expect(formatted).toMatch(/^<h2>Заголовок материала<\/h2>$/m);
        expect(formatted).toMatch(/^<ol>$/m);
        expect(formatted).toMatch(/^<p>\[gallery id="1" name="1"\]<\/p>$/m);

        // Отступы растут с вложенностью: ol(0) → li(4) → p(8) → текст(12)
        expect(indentOfLineContaining('<ol>', lines)).toBe(0);
        expect(indentOfLineMatching(/^<li>$/, lines)).toBe(4);
        expect(indentOfLineMatching(/^<p>$/, lines)).toBe(8);
        expect(indentOfLineContaining('Текст', lines)).toBe(12);

        // Ссылки остаются целыми элементами (атрибуты tiptap не тронуты)
        expect(formatted).toMatch(/^ +<a [^>]*href="\/a"[^>]*>one<\/a> и$/m);

        // Картинка не разорвана, шорткод не тронут
        expect(formatted).toMatch(/^<img src="\/storage\/pic\.jpg"[^>]*>$/m);
        expect(formatted).toContain('[gallery id="1" name="1"]');
    });

    it('round-trip: format → setContent → getHTML даёт тот же DOM (с точностью до whitespace)', async () => {
        const original = adapter.getHTML();

        const formatted = await useCase.getCurrentHtml();
        adapter.setContent(formatted);
        const roundTripped = adapter.getHTML();

        // Контрольная ветка: тот же setContent без форматирования — исключает
        // собственные артефакты setContent из сравнения
        adapter.setContent(original);
        const originalViaSetContent = adapter.getHTML();

        expect(parseNormalized(roundTripped).isEqualNode(parseNormalized(originalViaSetContent))).toBe(true);
        expect(parseNormalizedIgnoringEmptyParagraphs(roundTripped).isEqualNode(
            parseNormalizedIgnoringEmptyParagraphs(original),
        )).toBe(true);
    });

    it('шорткоды распознаются после форматирования и round-trip', async () => {
        const formatted = await useCase.getCurrentHtml();
        adapter.setContent(formatted);
        const roundTripped = adapter.getHTML();

        for (const html of [formatted, roundTripped]) {
            const galleries = [...html.matchAll(GALLERY_SHORTCODE_RE)];
            expect(galleries).toHaveLength(1);
            expect(galleries[0][1]).toBe('1');
            expect(galleries[0][2]).toBe('1');
            expect(html.match(FORM_SHORTCODE_RE) ?? []).toHaveLength(0);
        }
    });

    it('applyHtml передаёт введённый текст в редактор без переформатирования', () => {
        const spy = vi.spyOn(adapter, 'setContent');
        const raw = '<p>как есть</p><p>[form id="2"]</p>';

        useCase.applyHtml(raw);

        expect(spy).toHaveBeenCalledWith(raw);
    });
});
