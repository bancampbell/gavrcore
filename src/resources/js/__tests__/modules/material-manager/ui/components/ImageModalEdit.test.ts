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

import ImageModal from '@/modules/MaterialManager/ui/components/ImageModal.vue';

const EDIT_DATA = {
    url: '/storage/uploads/pic.jpg',
    alt: 'Кот',
    title: 'Подсказка',
    width: '320',
    height: '200',
    align: 'center',
    float: '',
    margin: '',
    _pos: 7,
};

const mountModal = (props: Record<string, any>) => mount(ImageModal, {
    props,
    global: { provide: { user: { id: 1 } } },
    attachTo: document.body,
});

const getInputs = () => Array.from(document.body.querySelectorAll('.image-modal .form-row input')) as HTMLInputElement[];

describe('ImageModal: редактирование существующего изображения', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        document.body.innerHTML = '';
    });

    it('открывается с заполненными полями из editData (монтирование с show=true)', () => {
        mountModal({ show: true, editData: EDIT_DATA });

        const inputs = getInputs();
        expect(inputs[0].value).toBe('/storage/uploads/pic.jpg');
        expect(inputs[1].value).toBe('Кот');
        expect(inputs[2].value).toBe('Подсказка');
        expect(inputs[3].value).toBe('320');
        expect(inputs[4].value).toBe('200');

        const selects = document.body.querySelectorAll('.image-modal select');
        expect((selects[0] as HTMLSelectElement).value).toBe('center');
    });

    it('открывается с пустыми полями без editData (режим вставки)', () => {
        mountModal({ show: true, editData: null });

        const inputs = getInputs();
        expect(inputs[0].value).toBe('');
        expect(inputs[1].value).toBe('');
        expect(inputs[3].value).toBe('');
    });

    it('заполняет поля при повторном открытии через toggle show (режим без remount)', async () => {
        const wrapper = mountModal({ show: false, editData: null });
        expect(getInputs().length).toBe(0);

        await wrapper.setProps({ show: true, editData: EDIT_DATA });

        const inputs = getInputs();
        expect(inputs[0].value).toBe('/storage/uploads/pic.jpg');
        expect(inputs[1].value).toBe('Кот');
        expect(inputs[3].value).toBe('320');
    });

    it('сбрасывает поля при открытии без editData после редактирования', async () => {
        const wrapper = mountModal({ show: false, editData: null });
        await wrapper.setProps({ show: true, editData: EDIT_DATA });
        expect(getInputs()[0].value).toBe('/storage/uploads/pic.jpg');

        await wrapper.setProps({ show: false });
        await wrapper.setProps({ show: true, editData: null });

        const inputs = getInputs();
        expect(inputs[0].value).toBe('');
        expect(inputs[1].value).toBe('');
    });

    it('подтверждение эмитит insert с новыми значениями, _pos и oldUrl', async () => {
        const wrapper = mountModal({ show: true, editData: EDIT_DATA });
        const inputs = getInputs();

        inputs[0].value = '/storage/uploads/new.jpg';
        inputs[0].dispatchEvent(new Event('input'));
        inputs[1].value = 'Новый alt';
        inputs[1].dispatchEvent(new Event('input'));
        await flushPromises();

        const insertBtn = document.body.querySelector('.image-modal .modal-footer .btn-primary') as HTMLButtonElement;
        insertBtn.click();
        await flushPromises();

        const events = wrapper.emitted('insert');
        expect(events).toBeTruthy();
        expect(events![0][0]).toMatchObject({
            url: '/storage/uploads/new.jpg',
            alt: 'Новый alt',
            title: 'Подсказка',
            width: '320',
            height: '200',
            align: 'center',
            oldUrl: '/storage/uploads/pic.jpg',
            _pos: 7,
        });
    });
});
