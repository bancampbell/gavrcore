import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { mount, flushPromises } from '@vue/test-utils';
import Index from '@/modules/GalleryManager/ui/Index.vue';
import { galleryApi } from '@/modules/GalleryManager/infrastructure/api/gallery-api';
import type { GalleryListItem, GalleryListResponse } from '@/modules/GalleryManager/ui/types';

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

function makeGalleries(count: number, offset = 0): GalleryListItem[] {
    return Array.from({ length: count }, (_, i) => ({
        id: offset + i + 1,
        title: `Gallery ${offset + i + 1}`,
        type: ['grid', 'slider', 'slideshow', 'switcher'][i % 4] as GalleryListItem['type'],
        status: i % 2 === 0,
        images_count: i,
        created_at: `2024-01-${String((i % 28) + 1).padStart(2, '0')}`,
    }));
}

function paginatorResponse(items: GalleryListItem[], overrides: Partial<GalleryListResponse> = {}): GalleryListResponse {
    return {
        data: items,
        from: items.length > 0 ? 1 : null,
        to: items.length,
        total: items.length,
        per_page: 10,
        current_page: 1,
        last_page: 1,
        ...overrides,
    };
}

describe('Index.vue', () => {
    const pageOne = paginatorResponse(makeGalleries(10), { from: 1, to: 10, total: 25, current_page: 1, last_page: 3 });
    const pageTwo = paginatorResponse(makeGalleries(10, 10), { from: 11, to: 20, total: 25, current_page: 2, last_page: 3 });

    beforeEach(() => {
        vi.clearAllMocks();
        vi.mocked(galleryApi.list).mockResolvedValue({ data: pageOne } as any);
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('loads galleries on mount', async () => {
        mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        expect(galleryApi.list).toHaveBeenCalledTimes(1);
        expect(galleryApi.list).toHaveBeenCalledWith({ search: undefined, type: undefined, status: undefined, page: 1, per_page: 10 });
    });

    it('renders page title', () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        expect(wrapper.text()).toContain('Менеджер галерей');
    });

    it('renders galleries from the current page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        expect(wrapper.vm.galleries).toHaveLength(10);
        expect(wrapper.text()).toContain('Показано 1 - 10 из 25');
        expect(wrapper.text()).toContain('1 / 3');
    });

    it('navigates to next page via server request', async () => {
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: pageOne } as any);
        vi.mocked(galleryApi.list).mockResolvedValueOnce({ data: pageTwo } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        await wrapper.vm.nextPage();
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: undefined, page: 2, per_page: 10 });
        expect(wrapper.vm.pagination.current_page).toBe(2);
        expect(wrapper.vm.galleries[0].id).toBe(11);
    });

    it('does not navigate past the last page', async () => {
        const lastPage = paginatorResponse(makeGalleries(5, 20), { from: 21, to: 25, total: 25, current_page: 3, last_page: 3 });
        vi.mocked(galleryApi.list).mockResolvedValue({ data: lastPage } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        await wrapper.vm.nextPage();
        await flushPromises();

        expect(galleryApi.list).toHaveBeenCalledTimes(1);
        expect(wrapper.vm.pagination.current_page).toBe(3);
    });

    it('applies type filter from the first page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.type = 'grid';
        await wrapper.vm.applyFilters(1);
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: 'grid', status: undefined, page: 1, per_page: 10 });
    });

    it('applies status filter from the first page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.status = '0';
        await wrapper.vm.applyFilters(1);
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: false, page: 1, per_page: 10 });
    });

    it('debounces search input and resets to the first page', async () => {
        vi.useFakeTimers();

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.search = 'Gallery 1';
        wrapper.vm.debounceSearch();
        await vi.advanceTimersByTimeAsync(300);
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: 'Gallery 1', type: undefined, status: undefined, page: 1, per_page: 10 });
    });

    it('changes per page and resets to the first page', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.perPage = 25;
        await wrapper.vm.changePerPage();
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: undefined, page: 1, per_page: 25 });
    });

    it('combines filters, search and page in one request', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.search = 'Alpha';
        wrapper.vm.filters.type = 'slider';
        wrapper.vm.filters.status = '1';
        await wrapper.vm.applyFilters(2);
        await flushPromises();

        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: 'Alpha', type: 'slider', status: true, page: 2, per_page: 10 });
    });

    it('resets filters and pagination', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.filters.search = 'test';
        wrapper.vm.filters.type = 'grid';
        wrapper.vm.resetFilters();
        await flushPromises();

        expect(wrapper.vm.filters.search).toBe('');
        expect(wrapper.vm.filters.type).toBe('');
        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: undefined, page: 1, per_page: 10 });
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

    it('does not toggle selection when clicking title link', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        const link = wrapper.find('a.title-text');
        expect(link.exists()).toBe(true);
        await link.trigger('click');
        expect(wrapper.vm.selectedIds).toHaveLength(0);
    });

    it('selects all items of the current page via checkbox', async () => {
        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.allSelected = true;
        await flushPromises();
        expect(wrapper.vm.selectedIds).toHaveLength(wrapper.vm.galleries.length);
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

    it('deletes selected galleries and reloads the first page', async () => {
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
        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: undefined, page: 1, per_page: 10 });
    });

    it('publishes selected galleries and reloads the current page', async () => {
        vi.mocked(galleryApi.publish).mockResolvedValue({ data: { success: true } } as any);

        const wrapper = mount(Index, { props: { user: {}, title: 'Галереи' } });
        await flushPromises();
        wrapper.vm.toggleSelect(3);
        await wrapper.vm.publishSelected();
        await flushPromises();

        expect(galleryApi.publish).toHaveBeenCalledWith(3);
        expect(wrapper.vm.notification.type).toBe('success');
        expect(galleryApi.list).toHaveBeenCalledTimes(2);
        expect(galleryApi.list).toHaveBeenLastCalledWith({ search: undefined, type: undefined, status: undefined, page: 1, per_page: 10 });
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
