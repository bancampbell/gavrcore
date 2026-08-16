import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import Index from '@/modules/GalleryManager/ui/Index.vue';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { GalleryListItem } from '@/modules/GalleryManager/ui/types';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: {
        template: '<a :href="href"><slot /></a>',
        props: ['href'],
    },
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

vi.mock('@/modules/GalleryManager/ui/components/GalleryModal.vue', () => ({
    default: {
        template: '<div class="gallery-modal"><slot /></div>',
        props: ['show', 'isEdit', 'galleryData'],
        emits: ['close', 'save'],
    },
}));

describe('Index.vue', () => {
    const mockGalleries: GalleryListItem[] = Array.from({ length: 25 }, (_, i) => ({
        id: i + 1,
        title: `Gallery ${i + 1}`,
        type: ['grid', 'slider', 'slideshow', 'switcher'][i % 4] as GalleryListItem['type'],
        status: i % 2 === 0,
        images_count: i,
        created_at: `2024-01-${String((i % 28) + 1).padStart(2, '0')}`,
    }));

    beforeEach(() => {
        vi.clearAllMocks();
        vi.mocked(galleryApi.list).mockResolvedValue({ data: mockGalleries } as any);
    });

    it('loads galleries on mount', async () => {
        mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        expect(galleryApi.list).toHaveBeenCalledTimes(1);
    });

    it('renders page title', () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        expect(wrapper.text()).toContain('Менеджер галерей');
    });

    it('paginates galleries (20 per page)', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        expect(wrapper.vm.paginatedGalleries).toHaveLength(20);
        expect(wrapper.vm.pagination.total).toBe(25);
        expect(wrapper.vm.pagination.last_page).toBe(2);
    });

    it('navigates to next page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        await wrapper.vm.nextPage();
        expect(wrapper.vm.currentPage).toBe(2);
        expect(wrapper.vm.paginatedGalleries).toHaveLength(5);
    });

    it('disables next page on last page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.currentPage = 2;
        expect(wrapper.vm.pagination.current_page).toBe(2);
        expect(wrapper.vm.pagination.last_page).toBe(2);
    });

    it('filters by search query', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.search = 'Gallery 1';
        await flushPromises();
        expect(wrapper.vm.filteredGalleries.every(g => g.title.includes('Gallery 1'))).toBe(true);
    });

    it('filters by type', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.type = 'grid';
        await flushPromises();
        expect(wrapper.vm.filteredGalleries.every(g => g.type === 'grid')).toBe(true);
    });

    it('filters by status', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.status = '1';
        await flushPromises();
        expect(wrapper.vm.filteredGalleries.every(g => g.status === true)).toBe(true);
    });

    it('resets filters and pagination', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.search = 'test';
        wrapper.vm.currentPage = 2;
        wrapper.vm.resetFilters();
        await flushPromises();
        expect(wrapper.vm.filters.search).toBe('');
        expect(wrapper.vm.currentPage).toBe(1);
    });

    it('selects single item', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        expect(wrapper.vm.selectedIds).toContain(1);
        expect(wrapper.vm.selectedIds).toHaveLength(1);
    });

    it('deselects item on second toggle', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        wrapper.vm.toggleSelect(1);
        expect(wrapper.vm.selectedIds).not.toContain(1);
    });

    it('selects all via checkbox', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.allSelected = true;
        await flushPromises();
        expect(wrapper.vm.selectedIds).toHaveLength(wrapper.vm.filteredGalleries.length);
    });

    it('opens create modal on button click', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        await wrapper.findAll('button').filter(b => b.text().includes('Создать галерею'))[0]?.trigger('click');
        expect(wrapper.vm.createModalOpen).toBe(true);
    });

    it('shows edit button only with single selection', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        expect(wrapper.text()).not.toContain('Редактировать');
        wrapper.vm.toggleSelect(1);
        await flushPromises();
        expect(wrapper.text()).toContain('Редактировать');
    });

    it('navigates to edit page for selected item', async () => {
        const { router } = await import('@inertiajs/vue3');
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(5);
        await flushPromises();
        wrapper.vm.editSelected();
        expect(router.visit).toHaveBeenCalledWith('/admin/galleries/5/edit');
    });

    it('deletes selected galleries', async () => {
        vi.mocked(galleryApi.destroy).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        wrapper.vm.toggleSelect(2);
        wrapper.vm.openDeleteModal();
        await wrapper.vm.confirmDelete();
        await flushPromises();

        expect(galleryApi.destroy).toHaveBeenCalledTimes(2);
        expect(wrapper.vm.selectedIds).toHaveLength(0);
    });

    it('publishes selected galleries', async () => {
        vi.mocked(galleryApi.publish).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(3);
        await wrapper.vm.publishSelected();
        await flushPromises();

        expect(galleryApi.publish).toHaveBeenCalledWith(3);
        expect(wrapper.vm.notification.type).toBe('success');
    });

    it('unpublishes selected galleries', async () => {
        vi.mocked(galleryApi.unpublish).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(4);
        await wrapper.vm.unpublishSelected();
        await flushPromises();

        expect(galleryApi.unpublish).toHaveBeenCalledWith(4);
    });

    it('reloads galleries after bulk action', async () => {
        vi.mocked(galleryApi.publish).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        await wrapper.vm.publishSelected();
        await flushPromises();

        expect(galleryApi.list).toHaveBeenCalledTimes(2); // initial + reload
    });

    it('formats date correctly', () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        expect(wrapper.vm.formatDate('2024-03-15')).toBe('15.03.2024');
        expect(wrapper.vm.formatDate(null)).toBe('—');
    });

    it('shows correct delete message for single item', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        wrapper.vm.openDeleteModal();
        expect(wrapper.vm.deleteMessage).toContain('выбранную галерею');
    });

    it('shows correct delete message for multiple items', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(1);
        wrapper.vm.toggleSelect(2);
        wrapper.vm.toggleSelect(3);
        wrapper.vm.openDeleteModal();
        expect(wrapper.vm.deleteMessage).toContain('3 галерей');
    });
});
