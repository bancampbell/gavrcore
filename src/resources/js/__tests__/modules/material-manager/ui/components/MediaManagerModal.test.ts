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
import MediaManagerModal from '@/modules/MaterialManager/ui/components/MediaManagerModal.vue';
import LinkModal from '@/modules/MaterialManager/ui/components/LinkModal.vue';
import ImageModal from '@/modules/MaterialManager/ui/components/ImageModal.vue';

const FILE_URL = '/storage/uploads/file1.jpg';

const pickFileInManager = async () => {
    const fileEl = document.body.querySelector('[data-file-path="file1.jpg"]') as HTMLElement;
    expect(fileEl).not.toBeNull();
    fileEl.click();
    await flushPromises();
};

const clickManagerInsert = async () => {
    const insertBtn = document.body.querySelector('.media-manager-footer .btn-insert') as HTMLButtonElement;
    expect(insertBtn).not.toBeNull();
    expect(insertBtn.disabled).toBe(false);
    insertBtn.click();
    await flushPromises();
};

describe('MediaManagerModal: выбор файла активирует вставку', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
        Element.prototype.scrollTo = vi.fn();
        (mediaApi.loadFolders as any).mockResolvedValue([]);
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: [{ name: 'file1.jpg', path: 'file1.jpg', type: 'file' }],
            total: 1,
            page: 1,
            per_page: 20,
            last_page: 1,
        });
    });

    it('кнопка «Вставить» неактивна до выбора, после выбора файла активна и эмитит select с url', async () => {
        const wrapper = mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 } },
            attachTo: document.body,
        });
        await flushPromises();

        const insertBtn = document.body.querySelector('.media-manager-footer .btn-insert') as HTMLButtonElement;
        expect(insertBtn.disabled).toBe(true);

        await pickFileInManager();
        expect(document.body.querySelector('.media-manager-footer')?.textContent).toContain('Выбран: file1.jpg');

        await clickManagerInsert();

        const events = wrapper.emitted('select');
        expect(events).toBeTruthy();
        expect(events![0]).toEqual([{ url: FILE_URL, name: 'file1.jpg', path: 'file1.jpg' }]);
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('LinkModal: файл из менеджера заполняет «Адрес», «Вставить ссылку» эмитит insert с url', async () => {
        const wrapper = mount(LinkModal, {
            props: { show: true, categories: [], materials: [] },
            global: { provide: { user: { id: 1 } } },
            attachTo: document.body,
        });
        await flushPromises();

        (document.body.querySelector('.link-modal .btn-icon') as HTMLButtonElement).click();
        await flushPromises();

        await pickFileInManager();
        await clickManagerInsert();

        const urlInput = document.body.querySelector('.link-modal .input-group input') as HTMLInputElement;
        expect(urlInput.value).toBe(FILE_URL);

        const insertLinkBtn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
            .find((b) => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
        insertLinkBtn.click();
        await flushPromises();

        const events = wrapper.emitted('insert');
        expect(events).toBeTruthy();
        expect(events![0]).toEqual([{ url: FILE_URL, text: 'file1.jpg', target: '_self', title: '' }]);
    });

    it('ImageModal: файл из менеджера заполняет «URL изображения», «Вставить» эмитит insert с url', async () => {
        const wrapper = mount(ImageModal, {
            props: { show: true },
            global: { provide: { user: { id: 1 } } },
            attachTo: document.body,
        });
        await flushPromises();

        (document.body.querySelector('.image-modal .btn-icon') as HTMLButtonElement).click();
        await flushPromises();

        await pickFileInManager();
        await clickManagerInsert();

        const urlInput = document.body.querySelector('.image-modal .input-group input') as HTMLInputElement;
        expect(urlInput.value).toBe(FILE_URL);

        const insertBtn = document.body.querySelector('.image-modal .modal-footer .btn-primary') as HTMLButtonElement;
        insertBtn.click();
        await flushPromises();

        const events = wrapper.emitted('insert');
        expect(events).toBeTruthy();
        expect((events![0] as any)[0].url).toBe(FILE_URL);
    });
});
