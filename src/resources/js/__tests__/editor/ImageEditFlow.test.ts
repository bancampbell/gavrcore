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
import { createEditorContext, provideEditorContext, type EditorContext } from '@/infrastructure/di/editorContext';

let context: EditorContext;

function createHarness(initialContent: string) {
    return defineComponent({
        setup() {
            context = createEditorContext();
            provideEditorContext(context);
            const content = ref(initialContent);
            return () => h(EditorComponent, {
                modelValue: content.value,
                'onUpdate:modelValue': (value: string) => { content.value = value; },
            });
        },
    });
}

const openImageModalForSelectedImage = async () => {
    const img = document.body.querySelector('.tiptap img') as HTMLImageElement;
    expect(img).not.toBeNull();
    img.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    await flushPromises();

    const imgBtn = document.body.querySelector('button[title="Вставить изображение"]') as HTMLButtonElement;
    expect(imgBtn).not.toBeNull();
    imgBtn.click();
    await flushPromises();

    expect(document.body.querySelector('.image-modal')).not.toBeNull();
};

const submitImageModal = async () => {
    const insertBtn = document.body.querySelector('.image-modal .modal-footer .btn-primary') as HTMLButtonElement;
    insertBtn.click();
    await flushPromises();
};

const getImageModalInputs = () =>
    Array.from(document.body.querySelectorAll('.image-modal .form-row input')) as HTMLInputElement[];

const countImages = (html: string) => (html.match(/<img/g) || []).length;

// Editor дебаунсит sync modelValue через setTimeout(300) — ждём, чтобы таймеры
// не срабатывали после разборки окружения jsdom.
const waitForEditorDebounce = () => new Promise((resolve) => setTimeout(resolve, 400));

describe('Editor: редактирование существующего изображения через диалог', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
        Element.prototype.scrollTo = vi.fn();
    });

    it('клик по картинке → модалка открывается с данными узла; подтверждение обновляет узел, а не вставляет новый', async () => {
        const Harness = createHarness(
            '<p>Текст до</p><img src="/storage/uploads/old.jpg" alt="Старое фото" title="Подсказка" width="320" height="200" style="width: 320px; height: 200px" /><p>Текст после</p>'
        );
        mount(Harness, {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();

        await openImageModalForSelectedImage();

        const inputs = getImageModalInputs();
        expect(inputs[0].value).toBe('/storage/uploads/old.jpg');
        expect(inputs[1].value).toBe('Старое фото');
        expect(inputs[2].value).toBe('Подсказка');
        expect(inputs[3].value).toBe('320');
        expect(inputs[4].value).toBe('200');

        inputs[0].value = '/storage/uploads/new.jpg';
        inputs[0].dispatchEvent(new Event('input'));
        inputs[1].value = 'Новое фото';
        inputs[1].dispatchEvent(new Event('input'));
        await flushPromises();

        await submitImageModal();

        const html = context.editorAdapter.getHTML();
        expect(html).toContain('src="/storage/uploads/new.jpg"');
        expect(html).toContain('alt="Новое фото"');
        expect(html).not.toContain('old.jpg');
        expect(countImages(html)).toBe(1);

        await waitForEditorDebounce();
    });

    it('обтекание и отступ текущей картинки подтягиваются в диалог и сохраняются при обновлении', async () => {
        const Harness = createHarness(
            '<p>текст</p><img src="/storage/uploads/float.jpg" alt="Плавучее" width="100" style="float: left; margin-right: 15px" /><p>после</p>'
        );
        mount(Harness, {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();

        await openImageModalForSelectedImage();

        const selects = document.body.querySelectorAll('.image-modal select') as NodeListOf<HTMLSelectElement>;
        expect(selects[1].value).toBe('left');

        const inputs = getImageModalInputs();
        expect(inputs[3].value).toBe('100');
        expect(inputs[5].value).toBe('15');

        inputs[1].value = 'Обновлённое';
        inputs[1].dispatchEvent(new Event('input'));
        await flushPromises();

        await submitImageModal();

        const html = context.editorAdapter.getHTML();
        expect(html).toContain('alt="Обновлённое"');
        expect(html).toContain('float: left');
        expect(html).toContain('margin-right: 15px');
        expect(countImages(html)).toBe(1);

        await waitForEditorDebounce();
    });

    it('из модалки редактирования файловый менеджер подсвечивает текущий файл', async () => {
        const Harness = createHarness(
            '<p>текст</p><img src="/storage/uploads/file1.jpg" alt="Текущий файл" /><p>после</p>'
        );
        vi.mocked(mediaApi.loadFolders).mockResolvedValue([]);
        vi.mocked(mediaApi.loadPaginatedContents).mockResolvedValue({
            data: [{ name: 'file1.jpg', path: 'file1.jpg', type: 'file' }],
            total: 1,
            page: 1,
            per_page: 20,
            last_page: 1,
        } as any);

        mount(Harness, {
            attachTo: document.body,
            global: { provide: { user: { id: 1 } } },
        });
        await flushPromises();

        await openImageModalForSelectedImage();

        const fileBtn = document.body.querySelector('.image-modal .input-group .btn-icon') as HTMLButtonElement;
        fileBtn.click();
        await flushPromises();
        await flushPromises();

        const fileEl = document.body.querySelector('.media-manager-modal [data-file-path="file1.jpg"]') as HTMLElement;
        expect(fileEl).not.toBeNull();
        expect(fileEl.classList.contains('bg-[#e6f0fa]')).toBe(true);

        const footer = document.body.querySelector('.media-manager-modal .media-manager-footer') as HTMLElement;
        expect(footer.textContent).toContain('Выбран: file1.jpg');

        await waitForEditorDebounce();
    });
});
