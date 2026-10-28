import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import { defineComponent, ref, h } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
}));

vi.mock('@/modules/MediaManager/infrastructure/api/media-api', () => ({
    mediaApi: {
        loadContents: vi.fn(),
        loadPaginatedContents: vi.fn(),
        loadFolders: vi.fn(),
        createFolder: vi.fn(),
        renameItem: vi.fn(),
        deleteItem: vi.fn(),
        deleteItems: vi.fn(),
        copyItem: vi.fn(),
        uploadFile: vi.fn(),
    },
}));

import { mediaApi } from '@/modules/MediaManager/infrastructure/api/media-api';
import EditorComponent from '@/components/shared/Editor/Index.vue';
import LinkModal from '@/modules/MaterialManager/ui/components/LinkModal.vue';
import { createEditorContext, provideEditorContext, type EditorContext } from '@/infrastructure/di/editorContext';

let context: EditorContext;
let lastEditPayload: any = null;

// jsdom не реализует innerText и layout-API. Шиманем innerText приближенно к
// браузеру (браузер возвращает отрисованный текст: схлопывает пробелы и
// обрезает концы), а getClientRects — пустыми прямоугольниками.
Object.defineProperty(HTMLElement.prototype, 'innerText', {
    configurable: true,
    get(this: HTMLElement) {
        return (this.textContent ?? '').replace(/\s+/g, ' ').trim();
    },
});
const fakeRect = { x: 0, y: 0, top: 0, left: 0, right: 0, bottom: 0, width: 0, height: 0, toJSON: () => ({}) };
(Node.prototype as any).getClientRects = () => [fakeRect];
(Node.prototype as any).getBoundingClientRect = () => fakeRect;
(Range.prototype as any).getClientRects = () => [fakeRect];
(Range.prototype as any).getBoundingClientRect = () => fakeRect;

function createHarness(initialContent: string, materials: any[] = []) {
    return defineComponent({
        setup() {
            context = createEditorContext();
            provideEditorContext(context);
            const content = ref(initialContent);
            const showLinkModal = ref(false);
            const editLinkData = ref<any>(null);
            const selectedLinkText = ref('');
            const editorRef = ref<any>(null);

            // проводка как в Edit.vue
            const handleEditLink = (data: any) => { editLinkData.value = data; showLinkModal.value = true; };
            const openLinkModal = (text?: string) => { editLinkData.value = null; selectedLinkText.value = text || ''; showLinkModal.value = true; };
            const updateLink = (data: any) => { lastEditPayload = data; editorRef.value?.updateExistingLink(data); };
            const insertLink = (data: any) => { editorRef.value?.setLinkOnSelection(data.url, data.text, data.target, data.title); };

            return () => h('div', [
                h(EditorComponent, {
                    ref: editorRef,
                    modelValue: content.value,
                    'onUpdate:modelValue': (value: string) => { content.value = value; },
                    'onEditLink': handleEditLink,
                    'onOpenLinkModal': openLinkModal,
                }),
                h(LinkModal as any, {
                    show: showLinkModal.value,
                    categories: [],
                    materials,
                    editData: editLinkData.value,
                    selectedText: selectedLinkText.value,
                    'onClose': () => { showLinkModal.value = false; editLinkData.value = null; selectedLinkText.value = ''; },
                    'onEdit': updateLink,
                    'onInsert': insertLink,
                }),
            ]);
        },
    });
}

const waitForEditorDebounce = () => new Promise((resolve) => setTimeout(resolve, 400));

const LINK_X_HTML = '<p>до <a href="/materials/5" target="_self" title="" rel="noopener noreferrer nofollow" class="text-blue-600 underline">Материал X</a> после</p>';

const MATERIALS = [
    { id: 1, title: 'Материал А', slug: 'material-a', category_id: null },
    { id: 2, title: 'Материал Б', slug: 'material-b', category_id: null },
];

const urlInput = () => document.body.querySelector('.link-modal .form-row input') as HTMLInputElement;

describe('Editor: редактирование существующей ссылки через диалог', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        lastEditPayload = null;
        document.body.innerHTML = '';
        Element.prototype.scrollTo = vi.fn();
        Element.prototype.scrollIntoView = vi.fn();
        (mediaApi.loadFolders as any).mockResolvedValue([]);
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: [
                { name: 'file1.jpg', path: 'file1.jpg', type: 'file' },
                { name: 'file30.jpg', path: 'file30.jpg', type: 'file' },
            ],
            total: 2,
            page: 1,
            per_page: 20,
            last_page: 1,
        });
    });

    const openDialogForLink = async () => {
        const a = document.body.querySelector('.tiptap a') as HTMLAnchorElement;
        a.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await flushPromises();
        (document.body.querySelector('button[title="Вставить ссылку"]') as HTMLButtonElement).click();
        await flushPromises();
    };

    const pickFileYInManager = async () => {
        (document.body.querySelector('.link-modal .btn-icon') as HTMLButtonElement).click();
        await flushPromises();
        await flushPromises();
        (document.body.querySelector('[data-file-path="file30.jpg"]') as HTMLElement).click();
        await flushPromises();
        (document.body.querySelector('.media-manager-footer .btn-insert') as HTMLButtonElement).click();
        await flushPromises();
    };

    const submitDialog = async () => {
        const submitBtn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
            .find(b => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
        submitBtn.click();
        await flushPromises();
        await waitForEditorDebounce();
    };

    it('обновляет тот же узел и повторное открытие с тулбара (без клика по ссылке) показывает новый URL', async () => {
        mount(createHarness(LINK_X_HTML), {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();
        await waitForEditorDebounce();

        await openDialogForLink();
        expect(urlInput().value).toBe('/materials/5');

        await pickFileYInManager();
        expect(urlInput().value).toBe('/storage/uploads/file30.jpg');

        await submitDialog();

        // тот же узел обновлён: один якорь, старый href исчез, текст сохранился
        const html = context.editorAdapter.getHTML();
        expect(html).toContain('/storage/uploads/file30.jpg');
        expect(html).not.toContain('/materials/5');
        expect(document.body.querySelectorAll('.tiptap a')).toHaveLength(1);

        // повторное открытие диалога с тулбара, НЕ кликая по ссылке заново
        (document.body.querySelector('button[title="Вставить ссылку"]') as HTMLButtonElement).click();
        await flushPromises();
        expect(urlInput().value).toBe('/storage/uploads/file30.jpg');
    });

    it('редактирование заменяет текст якоря целиком: многоузловый якорь → один якорь с новым текстом', async () => {
        mount(createHarness('<p>Материал А</p>', MATERIALS), {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();
        await waitForEditorDebounce();

        const ed = () => context.editorAdapter.getEditor()!;
        const submit = async () => {
            const btn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
                .find(b => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
            btn.click();
            await flushPromises();
            await waitForEditorDebounce();
        };
        const textInput = () => Array.from(document.body.querySelectorAll('.link-modal .form-row input'))[1] as HTMLInputElement;

        // 1. вставка ссылки с текстом «А»: выделяем текст, тулбар → модалка → submit
        ed().commands.setTextSelection({ from: 1, to: 1 + 'Материал А'.length });
        (document.body.querySelector('button[title="Вставить ссылку"]') as HTMLButtonElement).click();
        await flushPromises();
        expect(textInput().value).toBe('Материал А');

        const urlField = document.body.querySelector('.link-modal .form-row input') as HTMLInputElement;
        urlField.value = '/a';
        urlField.dispatchEvent(new Event('input'));
        await flushPromises();
        await submit();

        expect(ed().state.doc.textContent).toBe('Материал А');
        expect(context.editorAdapter.getHTML()).toContain('href="/a"');

        // 2. якорь становится многоузловым: «А» внутри ссылки делаем жирным
        const end = 1 + 'Материал А'.length;
        ed().chain().focus().setTextSelection({ from: end - 1, to: end }).toggleBold().run();
        await flushPromises();
        expect(context.editorAdapter.getHTML()).toContain('<strong>А</strong>');

        // 3. открываем диалог по существующей ссылке
        const a = document.body.querySelector('.tiptap a') as HTMLAnchorElement;
        a.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await flushPromises();
        (document.body.querySelector('button[title="Вставить ссылку"]') as HTMLButtonElement).click();
        await flushPromises();
        expect(urlInput().value).toBe('/a');

        // 4. выбираем другой материал «Б» и меняем текст ссылки на «Б»
        const searchInput = document.body.querySelector('.link-modal .search-input') as HTMLInputElement;
        searchInput.value = 'Материал Б';
        searchInput.dispatchEvent(new Event('input'));
        await flushPromises();
        (document.body.querySelector('.link-modal .search-button') as HTMLButtonElement).click();
        await flushPromises();
        (document.body.querySelector('.link-modal .material-item') as HTMLElement).click();
        await flushPromises();
        expect(urlInput().value).toBe('/material-b');

        textInput().value = 'Б';
        textInput().dispatchEvent(new Event('input'));
        await flushPromises();
        await submit();

        // 5. в документе ОДИН якорь с текстом «Б»: никакого «Материал АБ»
        expect(lastEditPayload).toMatchObject({ oldText: 'Материал А', newText: 'Б', newUrl: '/material-b' });
        expect(document.body.querySelectorAll('.tiptap a')).toHaveLength(1);
        const html = context.editorAdapter.getHTML();
        expect(html).toContain('href="/material-b"');
        expect(html).not.toContain('href="/a"');
        const docText = ed().state.doc.textContent;
        expect(docText).not.toContain('АБ');
        expect(docText).toBe('Б');
        expect((document.body.querySelector('.tiptap a') as HTMLAnchorElement).textContent).toBe('Б');
    });

    it('якорь из 3 слов: открытие по каретке внутри слова подставляет все 3 слова, замена — целиком', async () => {
        mount(createHarness('<p>до <a href="/a">Первое <strong>второе</strong> третье</a> после</p>', MATERIALS), {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();
        await waitForEditorDebounce();

        const ed = () => context.editorAdapter.getEditor()!;
        const submit = async () => {
            const btn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
                .find(b => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
            btn.click();
            await flushPromises();
            await waitForEditorDebounce();
        };
        const textInput = () => Array.from(document.body.querySelectorAll('.link-modal .form-row input'))[1] as HTMLInputElement;

        // клик по нейтральному месту сбрасывает выделение → диалог пойдёт через getLinkAt
        (document.body.querySelector('.tiptap p') as HTMLElement).dispatchEvent(new MouseEvent('click', { bubbles: true }));
        await flushPromises();

        // каретка внутри среднего слова «второе»
        ed().commands.setTextSelection(14);

        (document.body.querySelector('button[title="Вставить ссылку"]') as HTMLButtonElement).click();
        await flushPromises();

        // поле «Текст ссылки» = ВЕСЬ якорь, а не кликнутое слово/кусок узла
        expect(urlInput().value).toBe('/a');
        expect(textInput().value).toBe('Первое второе третье');

        // подтверждение с новым материалом
        const searchInput = document.body.querySelector('.link-modal .search-input') as HTMLInputElement;
        searchInput.value = 'Материал Б';
        searchInput.dispatchEvent(new Event('input'));
        await flushPromises();
        (document.body.querySelector('.link-modal .search-button') as HTMLButtonElement).click();
        await flushPromises();
        (document.body.querySelector('.link-modal .material-item') as HTMLElement).click();
        await flushPromises();

        textInput().value = 'Б';
        textInput().dispatchEvent(new Event('input'));
        await flushPromises();
        await submit();

        // якорь заменён целиком: один якорь, новый href и текст
        expect(document.body.querySelectorAll('.tiptap a')).toHaveLength(1);
        const html = context.editorAdapter.getHTML();
        expect(html).toContain('href="/material-b"');
        expect(html).not.toContain('href="/a"');
        expect(ed().state.doc.textContent).toBe('до Б после');
        expect((document.body.querySelector('.tiptap a') as HTMLAnchorElement).textContent).toBe('Б');
    });
});
