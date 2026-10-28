import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: { template: '<a :href="href"><slot /></a>', props: ['href'] },
}));

vi.mock('@/layouts/AdminLayout.vue', () => ({
    default: {
        template: '<div class="admin-layout"><slot /></div>',
        props: ['user'],
    },
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
import LinkModal from '@/modules/MaterialManager/ui/components/LinkModal.vue';

const SELECTED_CLASS = 'bg-[#e6f0fa]';

const makeFiles = (n: number) => Array.from({ length: n }, (_, i) => ({
    name: `file${i + 1}.jpg`,
    path: `file${i + 1}.jpg`,
    type: 'file',
}));

const editDataWith = (url: string) => ({
    oldText: 'текст',
    url,
    text: 'текст',
    target: '_self',
    title: '',
});

describe('LinkModal: файловый менеджер и прокрутка к выбранному файлу', () => {
    let scrollIntoView: ReturnType<typeof vi.fn>;

    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
        Element.prototype.scrollTo = vi.fn();
        scrollIntoView = vi.fn();
        Element.prototype.scrollIntoView = scrollIntoView;
        (mediaApi.loadFolders as any).mockResolvedValue([]);
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: makeFiles(30),
            total: 30,
            page: 1,
            per_page: 50,
            last_page: 1,
        });
    });

    const mountModal = (props: Record<string, any>) => mount(LinkModal, {
        props,
        global: { provide: { user: { id: 1 } } },
        attachTo: document.body,
    });

    const clickFileManagerButton = async () => {
        const btn = document.body.querySelector('.link-modal .btn-icon') as HTMLButtonElement;
        btn.click();
        await flushPromises();
        await flushPromises();
    };

    const expectScrolledTo = (path: string) => {
        const el = document.body.querySelector(`[data-file-path="${path}"]`) as HTMLElement;
        expect(el).not.toBeNull();
        expect(el.classList.contains(SELECTED_CLASS)).toBe(true);
        expect(scrollIntoView).toHaveBeenCalledWith({ block: 'nearest' });
        expect(scrollIntoView.mock.contexts[scrollIntoView.mock.calls.length - 1]).toBe(el);
    };

    it('первое открытие: подсвечивает текущий href и прокручивает к нему', async () => {
        mountModal({
            show: true,
            categories: [],
            materials: [],
            editData: editDataWith('/storage/uploads/file30.jpg'),
        });
        await flushPromises();

        await clickFileManagerButton();

        expectScrolledTo('file30.jpg');
    });

    it('второе открытие: синхронизация и прокрутка повторяются для нового href', async () => {
        const wrapper = mountModal({
            show: true,
            categories: [],
            materials: [],
            editData: editDataWith('/storage/uploads/file1.jpg'),
        });
        await flushPromises();

        // первая сессия: открыли менеджер, закрыли кнопкой «Отмена»
        await clickFileManagerButton();
        expectScrolledTo('file1.jpg');
        scrollIntoView.mockClear();

        const cancelBtn = Array.from(document.body.querySelectorAll('.media-manager-footer button'))
            .find(b => b.textContent?.trim() === 'Отмена') as HTMLButtonElement;
        cancelBtn.click();
        await flushPromises();

        // закрыли и заново открыли диалог ссылки с другим href
        await wrapper.setProps({ show: false });
        await wrapper.setProps({ show: true, editData: editDataWith('/storage/uploads/file30.jpg') });
        await flushPromises();

        await clickFileManagerButton();

        expectScrolledTo('file30.jpg');
    });

    it('href изменился на уже открытом менеджере: повторная синхронизация и прокрутка', async () => {
        mountModal({
            show: true,
            categories: [],
            materials: [],
            editData: editDataWith('/storage/uploads/file1.jpg'),
        });
        await flushPromises();

        await clickFileManagerButton();
        expectScrolledTo('file1.jpg');
        scrollIntoView.mockClear();

        // пользователь изменил адрес в поле «Адрес» (v-model → selectedUrl менеджера)
        const urlInput = document.body.querySelector('.link-modal .form-row input') as HTMLInputElement;
        urlInput.value = '/storage/uploads/file30.jpg';
        urlInput.dispatchEvent(new Event('input'));
        await flushPromises();
        await flushPromises();

        expectScrolledTo('file30.jpg');
    });

    it('файл на другой странице списка: переключает страницу и прокручивает к файлу', async () => {
        const page1 = makeFiles(20);
        const page2 = Array.from({ length: 10 }, (_, i) => ({
            name: `file${21 + i}.jpg`,
            path: `file${21 + i}.jpg`,
            type: 'file',
        }));
        // реальный сценарий: per_page = 20, файлов 30, выбранный — на 2-й странице
        (mediaApi.loadPaginatedContents as any).mockImplementation((path: string, page: number) =>
            Promise.resolve({
                data: page === 1 ? page1 : page2,
                total: 30,
                page,
                per_page: 20,
                last_page: 2,
            })
        );

        mountModal({
            show: true,
            categories: [],
            materials: [],
            editData: editDataWith('/storage/uploads/file30.jpg'),
        });
        await flushPromises();

        await clickFileManagerButton();

        const pagesRequested = (mediaApi.loadPaginatedContents as any).mock.calls.map((c: any[]) => c[1]);
        expect(pagesRequested).toContain(2);

        expectScrolledTo('file30.jpg');
    });
});
