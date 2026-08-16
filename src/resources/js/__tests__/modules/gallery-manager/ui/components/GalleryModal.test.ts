import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import GalleryModal from '@/modules/GalleryManager/ui/components/GalleryModal.vue';

describe('GalleryModal', () => {
    beforeEach(() => {
        document.body.innerHTML = '';
    });

    afterEach(() => {
        document.body.innerHTML = '';
    });

    it('does not render when show is false', () => {
        const wrapper = mount(GalleryModal, {
            props: { show: false },
            attachTo: document.body,
        });
        expect(wrapper.find('.modal-overlay').exists()).toBe(false);
    });

    it('renders create mode title when isEdit is false', () => {
        mount(GalleryModal, {
            props: { show: true, isEdit: false },
            attachTo: document.body,
        });
        expect(document.body.textContent).toContain('Создать галерею');
    });

    it('renders edit mode title when isEdit is true', () => {
        mount(GalleryModal, {
            props: { show: true, isEdit: true },
            attachTo: document.body,
        });
        expect(document.body.textContent).toContain('Редактировать галерею');
    });

    it('initializes form with defaults on create', () => {
        mount(GalleryModal, {
            props: { show: true, isEdit: false },
            attachTo: document.body,
        });
        const input = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const select = document.body.querySelector('select') as HTMLSelectElement;

        expect(input.value).toBe('');
        expect(select.value).toBe('grid');
    });

    it('initializes form with galleryData on edit', async () => {
        mount(GalleryModal, {
            props: {
                show: true,
                isEdit: true,
                galleryData: { title: 'Existing', type: 'slider', status: false },
            },
            attachTo: document.body,
        });
        await flushPromises();

        const input = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const select = document.body.querySelector('select') as HTMLSelectElement;

        expect(input.value).toBe('Existing');
        expect(select.value).toBe('slider');
    });

    it('emits close on overlay click', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const overlay = document.body.querySelector('.modal-overlay');
        expect(overlay).not.toBeNull();

        await (overlay as HTMLElement).dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('emits close on close button click', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const btn = document.body.querySelector('.modal-close');
        expect(btn).not.toBeNull();

        await (btn as HTMLElement).dispatchEvent(new MouseEvent('click', { bubbles: true }));
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('emits close on cancel button click', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const btn = document.body.querySelector('.btn-cancel');
        expect(btn).not.toBeNull();

        await (btn as HTMLElement).dispatchEvent(new MouseEvent('click', { bubbles: true }));
        expect(wrapper.emitted('close')).toBeTruthy();
    });

    it('emits save with form data on save click', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const input = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const select = document.body.querySelector('select') as HTMLSelectElement;
        const btn = document.body.querySelector('.btn-primary') as HTMLButtonElement;

        input.value = 'My Gallery';
        input.dispatchEvent(new Event('input'));
        select.value = 'slideshow';
        select.dispatchEvent(new Event('change'));

        await flushPromises();
        btn.click();
        await flushPromises();

        expect(wrapper.emitted('save')).toHaveLength(1);
        expect(wrapper.emitted('save')![0]).toEqual([{
            title: 'My Gallery',
            type: 'slideshow',
            status: true,
        }]);
    });

    it('does not emit save when title is empty', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const btn = document.body.querySelector('.btn-primary') as HTMLButtonElement;

        btn.click();
        await flushPromises();

        expect(wrapper.emitted('save')).toBeFalsy();
    });

    it('disables save button when title is empty', () => {
        mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const btn = document.body.querySelector('.btn-primary') as HTMLButtonElement;

        expect(btn.disabled).toBe(true);
    });

    it('toggles status on click', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true },
            attachTo: document.body,
        });
        const toggle = document.body.querySelector('.admin-toggle') as HTMLButtonElement;
        const input = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const btn = document.body.querySelector('.btn-primary') as HTMLButtonElement;

        toggle.click();
        await flushPromises();

        input.value = 'Test';
        input.dispatchEvent(new Event('input'));
        await flushPromises();

        btn.click();
        await flushPromises();

        const savePayload = wrapper.emitted('save')![0][0] as { status: boolean };
        expect(savePayload.status).toBe(false);
    });

    it('shows loading state on save button', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true, isEdit: false },
            attachTo: document.body,
        });
        // @ts-expect-error
        wrapper.vm.loading = true;
        await flushPromises();

        const btn = document.body.querySelector('.btn-primary') as HTMLButtonElement;
        expect(btn.textContent).toContain('Сохранение');
    });

    it('resets form on close in create mode', async () => {
        const wrapper = mount(GalleryModal, {
            props: { show: true, isEdit: false },
            attachTo: document.body,
        });
        const input = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const select = document.body.querySelector('select') as HTMLSelectElement;

        input.value = 'Temp';
        input.dispatchEvent(new Event('input'));
        select.value = 'slider';
        select.dispatchEvent(new Event('change'));
        await flushPromises();

        await wrapper.setProps({ show: false });
        await flushPromises();
        await wrapper.setProps({ show: true });
        await flushPromises();

        const newInput = document.body.querySelector('input[type="text"]') as HTMLInputElement;
        const newSelect = document.body.querySelector('select') as HTMLSelectElement;

        expect(newInput.value).toBe('');
        expect(newSelect.value).toBe('grid');
    });
});
