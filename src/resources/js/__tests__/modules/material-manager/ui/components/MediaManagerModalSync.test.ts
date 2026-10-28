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

const SELECTED_CLASS = 'bg-[#e6f0fa]';

describe('MediaManagerModal: синхронизация выбора с selectedUrl', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
        Element.prototype.scrollTo = vi.fn();
        Element.prototype.scrollIntoView = vi.fn();
        (mediaApi.loadFolders as any).mockResolvedValue([]);
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: [
                { name: 'file1.jpg', path: 'file1.jpg', type: 'file' },
                { name: 'file2.jpg', path: 'file2.jpg', type: 'file' },
            ],
            total: 2,
            page: 1,
            per_page: 20,
            last_page: 1,
        });
    });

    it('подсвечивает файл, соответствующий selectedUrl, и активирует «Вставить»', async () => {
        const wrapper = mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 }, selectedUrl: '/storage/uploads/file1.jpg' },
            attachTo: document.body,
        });
        await flushPromises();
        await flushPromises();

        const selectedEl = document.body.querySelector('[data-file-path="file1.jpg"]') as HTMLElement;
        expect(selectedEl).not.toBeNull();
        expect(selectedEl.classList.contains(SELECTED_CLASS)).toBe(true);

        const otherEl = document.body.querySelector('[data-file-path="file2.jpg"]') as HTMLElement;
        expect(otherEl.classList.contains(SELECTED_CLASS)).toBe(false);

        const footer = document.body.querySelector('.media-manager-footer') as HTMLElement;
        expect(footer.textContent).toContain('Выбран: file1.jpg');

        const insertBtn = footer.querySelector('.btn-insert') as HTMLButtonElement;
        expect(insertBtn.disabled).toBe(false);

        insertBtn.click();
        await flushPromises();

        const events = wrapper.emitted('select');
        expect(events).toBeTruthy();
        expect(events![0]).toEqual([{ url: '/storage/uploads/file1.jpg', name: 'file1.jpg', path: 'file1.jpg' }]);
    });

    it('принимает absolute URL текущего файла', async () => {
        mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 }, selectedUrl: 'https://example.org/storage/uploads/file2.jpg' },
            attachTo: document.body,
        });
        await flushPromises();
        await flushPromises();

        const selectedEl = document.body.querySelector('[data-file-path="file2.jpg"]') as HTMLElement;
        expect(selectedEl).not.toBeNull();
        expect(selectedEl.classList.contains(SELECTED_CLASS)).toBe(true);
    });

    it('не подсвечивает ничего, если selectedUrl не соответствует файлу', async () => {
        mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 }, selectedUrl: '/storage/uploads/missing.jpg' },
            attachTo: document.body,
        });
        await flushPromises();
        await flushPromises();

        const fileEls = document.body.querySelectorAll('[data-file-path]');
        fileEls.forEach((el) => {
            expect((el as HTMLElement).classList.contains(SELECTED_CLASS)).toBe(false);
        });

        const insertBtn = document.body.querySelector('.media-manager-footer .btn-insert') as HTMLButtonElement;
        expect(insertBtn.disabled).toBe(true);
    });

    it('прокручивает список к выбранному файлу при синхронизации', async () => {
        const files = Array.from({ length: 30 }, (_, i) => ({
            name: `file${i + 1}.jpg`,
            path: `file${i + 1}.jpg`,
            type: 'file',
        }));
        (mediaApi.loadPaginatedContents as any).mockResolvedValue({
            data: files,
            total: 30,
            page: 1,
            per_page: 50,
            last_page: 1,
        });

        const scrollIntoView = vi.fn();
        Element.prototype.scrollIntoView = scrollIntoView;

        mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 }, selectedUrl: '/storage/uploads/file30.jpg' },
            attachTo: document.body,
        });
        await flushPromises();
        await flushPromises();

        const selectedEl = document.body.querySelector('[data-file-path="file30.jpg"]') as HTMLElement;
        expect(selectedEl).not.toBeNull();
        expect(selectedEl.classList.contains(SELECTED_CLASS)).toBe(true);

        expect(scrollIntoView).toHaveBeenCalledTimes(1);
        expect(scrollIntoView).toHaveBeenCalledWith({ block: 'nearest' });
        expect(scrollIntoView.mock.contexts[0]).toBe(selectedEl);
    });

    it('подсвечивает файл при повторном открытии модалки', async () => {
        const wrapper = mount(MediaManagerModal, {
            props: { show: true, user: { id: 1 }, selectedUrl: '/storage/uploads/file1.jpg' },
            attachTo: document.body,
        });
        await flushPromises();
        await flushPromises();
        expect((document.body.querySelector('[data-file-path="file1.jpg"]') as HTMLElement).className).toContain(SELECTED_CLASS);

        await wrapper.setProps({ show: false });
        await wrapper.setProps({ show: true });
        await flushPromises();
        await flushPromises();

        expect((document.body.querySelector('[data-file-path="file1.jpg"]') as HTMLElement).classList.contains(SELECTED_CLASS)).toBe(true);
    });
});
