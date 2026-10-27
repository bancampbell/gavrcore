import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';

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

import LinkModal from '@/modules/MaterialManager/ui/components/LinkModal.vue';

const EDIT_DATA = {
    oldText: 'текст ссылки',
    url: 'https://example.com',
    text: 'текст ссылки',
    target: '_blank',
    title: 'Подсказка',
};

const mountModal = (props: Record<string, any>) => mount(LinkModal, {
    props,
    global: { provide: { user: { id: 1 } } },
    attachTo: document.body,
});

describe('LinkModal: редактирование существующей ссылки', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
    });

    it('открывается с заполненными полями href/text/target/title из editData', () => {
        mountModal({ show: true, categories: [], materials: [], editData: EDIT_DATA });

        const inputs = document.body.querySelectorAll('.link-modal .form-row input') as NodeListOf<HTMLInputElement>;
        expect(inputs[0].value).toBe('https://example.com');
        expect(inputs[1].value).toBe('текст ссылки');
        expect(inputs[2].value).toBe('Подсказка');

        const targetSelect = document.body.querySelector('.link-modal select') as HTMLSelectElement;
        expect(targetSelect.value).toBe('_blank');
    });

    it('подтверждение эмитит edit (обновление), а не insert', async () => {
        const wrapper = mountModal({ show: true, categories: [], materials: [], editData: EDIT_DATA });

        const inputs = document.body.querySelectorAll('.link-modal .form-row input') as NodeListOf<HTMLInputElement>;
        inputs[0].value = 'https://new.example.com';
        inputs[0].dispatchEvent(new Event('input'));
        inputs[2].value = 'Новая подсказка';
        inputs[2].dispatchEvent(new Event('input'));
        await flushPromises();

        const submitBtn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
            .find(b => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
        submitBtn.click();
        await flushPromises();

        const editEvents = wrapper.emitted('edit');
        expect(editEvents).toBeTruthy();
        expect(editEvents![0][0]).toEqual({
            oldText: 'текст ссылки',
            newUrl: 'https://new.example.com',
            newText: 'текст ссылки',
            newTarget: '_blank',
            newTitle: 'Новая подсказка',
        });
        expect(wrapper.emitted('insert')).toBeFalsy();
    });

    it('без editData подставляет выделенный текст и эмитит insert', async () => {
        const wrapper = mountModal({ show: true, categories: [], materials: [], editData: null, selectedText: 'выделенный текст' });

        const inputs = document.body.querySelectorAll('.link-modal .form-row input') as NodeListOf<HTMLInputElement>;
        expect(inputs[1].value).toBe('выделенный текст');

        inputs[0].value = 'https://example.com';
        inputs[0].dispatchEvent(new Event('input'));
        await flushPromises();

        const submitBtn = Array.from(document.body.querySelectorAll('.link-modal .modal-footer button'))
            .find(b => b.textContent?.trim() === 'Вставить ссылку') as HTMLButtonElement;
        submitBtn.click();
        await flushPromises();

        const insertEvents = wrapper.emitted('insert');
        expect(insertEvents).toBeTruthy();
        expect(insertEvents![0][0]).toEqual({
            url: 'https://example.com',
            text: 'выделенный текст',
            target: '_self',
            title: '',
        });
    });
});
