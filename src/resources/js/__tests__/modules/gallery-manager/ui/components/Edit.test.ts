import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import Edit from '@/modules/GalleryManager/ui/Edit.vue';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { Gallery, GalleryImage } from '@/modules/GalleryManager/ui/types';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    router: { visit: vi.fn() },
}));

vi.mock('@/modules/GalleryManager/infrastructure/api/gallery-api');

vi.mock('@/layouts/AdminLayout.vue', () => ({
    default: {
        template: '<div class="admin-layout"><slot /></div>',
        props: ['user'],
    },
}));

vi.mock('@/components/shared/Toast.vue', () => ({
    default: {
        template: '<div class="toast"><slot /></div>',
        props: ['show', 'message', 'type'],
    },
}));

vi.mock('@/components/shared/ConfirmModal.vue', () => ({
    default: {
        template: '<div class="confirm-modal"><slot /></div>',
        props: ['isOpen', 'title', 'message', 'confirmText', 'type', 'loading'],
        emits: ['close', 'confirm'],
    },
}));

describe('Edit.vue', () => {
    const mockImages: GalleryImage[] = [
        { id: 1, gallery_id: 10, image_path: '/img/a.jpg', title: 'Image A', description: 'Desc A', alt_text: 'Alt A', link: null, ordering: 1, status: true },
        { id: 2, gallery_id: 10, image_path: '/img/b.jpg', title: 'Image B', description: null, alt_text: null, link: 'https://link.com', ordering: 2, status: true },
    ];

    const mockGallery: Gallery = {
        id: 10,
        title: 'Test Gallery',
        type: 'grid',
        status: true,
        settings: { gutter: 20 },
        images: mockImages,
        created_at: '2024-01-01',
        updated_at: '2024-01-01',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders with gallery data', () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        expect(wrapper.text()).toContain('Edit Gallery');
        expect((wrapper.findAll('input[type="text"]')[0].element as HTMLInputElement).value).toBe('Test Gallery');
    });

    it('initializes images from props', () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        expect(wrapper.vm.images).toHaveLength(2);
    });

    it('selects first image on mount', () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        expect(wrapper.vm.selectedImage?.id).toBe(1);
    });

    it('switches active tab', async () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        expect(wrapper.vm.activeTab).toBe('media');
        await wrapper.findAll('button').filter(b => b.text().includes('НАСТРОЙКИ'))[0]?.trigger('click');
        expect(wrapper.vm.activeTab).toBe('settings');
    });

    it('selects image on click', async () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.selectImage(mockImages[1]);
        expect(wrapper.vm.selectedImage?.id).toBe(2);
    });

    it('validates empty title on save', async () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.form.title = '';
        await wrapper.findAll('button').filter(b => b.text().includes('Сохранить') && !b.text().includes('закрыть'))[0]?.trigger('click');
        expect(wrapper.vm.notification.type).toBe('error');
    });

    it('calls update api on save', async () => {
        vi.mocked(galleryApi.update).mockResolvedValueOnce({ data: { success: true } } as any);
        vi.mocked(galleryApi.updateImage).mockResolvedValueOnce({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        await wrapper.findAll('button').filter(b => b.text().includes('Сохранить') && !b.text().includes('закрыть'))[0]?.trigger('click');
        await flushPromises();

        expect(galleryApi.update).toHaveBeenCalledWith(10, expect.objectContaining({
            title: 'Test Gallery',
            type: 'grid',
            status: true,
        }));
    });

    it('calls updateImage for each image on save', async () => {
        vi.mocked(galleryApi.update).mockResolvedValueOnce({ data: { success: true } } as any);
        vi.mocked(galleryApi.updateImage).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        await wrapper.findAll('button').filter(b => b.text().includes('Сохранить') && !b.text().includes('закрыть'))[0]?.trigger('click');
        await flushPromises();

        expect(galleryApi.updateImage).toHaveBeenCalledTimes(2);
    });

    it('shows success notification after save', async () => {
        vi.mocked(galleryApi.update).mockResolvedValueOnce({ data: { success: true } } as any);
        vi.mocked(galleryApi.updateImage).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        await wrapper.findAll('button').filter(b => b.text().includes('Сохранить') && !b.text().includes('закрыть'))[0]?.trigger('click');
        await flushPromises();

        expect(wrapper.vm.notification.type).toBe('success');
        expect(wrapper.vm.notification.message).toBe('Сохранено');
    });

    it('shows error notification on save failure', async () => {
        vi.mocked(galleryApi.update).mockRejectedValueOnce(new Error('Server error'));

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        await wrapper.findAll('button').filter(b => b.text().includes('Сохранить') && !b.text().includes('закрыть'))[0]?.trigger('click');
        await flushPromises();

        expect(wrapper.vm.notification.type).toBe('error');
        expect(wrapper.vm.notification.message).toBe('Ошибка при сохранении');
    });

    it('navigates back on cancel', async () => {
        const { router } = await import('@inertiajs/vue3');
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        await wrapper.findAll('button').filter(b => b.text() === 'Закрыть')[0]?.trigger('click');
        expect(router.visit).toHaveBeenCalledWith('/admin/galleries');
    });

    it('shows confirm modal before deleting image', () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.deleteImage(mockImages[0]);
        expect(wrapper.vm.confirmModal.isOpen).toBe(true);
        expect(wrapper.vm.confirmModal.title).toBe('Удалить изображение?');
    });

    it('deletes image and removes from list on confirm', async () => {
        vi.mocked(galleryApi.deleteImage).mockResolvedValueOnce({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.deleteImage(mockImages[0]);
        await wrapper.vm.confirmModal.onConfirm();
        await flushPromises();

        expect(galleryApi.deleteImage).toHaveBeenCalledWith(10, 1);
        expect(wrapper.vm.images).toHaveLength(1);
        expect(wrapper.vm.images.find((i: any) => i.id === 1)).toBeUndefined();
    });

    it('selects next image after deletion of selected', async () => {
        vi.mocked(galleryApi.deleteImage).mockResolvedValueOnce({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.deleteImage(mockImages[0]);
        await wrapper.vm.confirmModal.onConfirm();
        await flushPromises();

        expect(wrapper.vm.selectedImage?.id).toBe(2);
    });

    it('clears selection when last image deleted', async () => {
        const singleImageGallery = { ...mockGallery, images: [mockImages[0]] };
        vi.mocked(galleryApi.deleteImage).mockResolvedValueOnce({ data: { success: true } } as any);

        const wrapper = mount(Edit, {
            props: { user: {}, gallery: singleImageGallery, title: 'Edit Gallery' },
        });
        wrapper.vm.deleteImage(mockImages[0]);
        await wrapper.vm.confirmModal.onConfirm();
        await flushPromises();

        expect(wrapper.vm.selectedImage).toBeNull();
    });

    it('triggers file input on add button click', async () => {
        const wrapper = mount(Edit, {
            props: { user: {}, gallery: mockGallery, title: 'Edit Gallery' },
        });
        const clickSpy = vi.spyOn(wrapper.vm.fileInput!, 'click');
        await wrapper.findAll('button').filter(b => b.text().includes('Добавить'))[0]?.trigger('click');
        expect(clickSpy).toHaveBeenCalled();
    });
});
