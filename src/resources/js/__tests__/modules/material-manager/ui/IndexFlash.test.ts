import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import Index from '@/modules/MaterialManager/ui/Index.vue';

const { pageProps, showNotification } = vi.hoisted(() => ({
    pageProps: { flash: undefined as { success?: string; error?: string } | undefined },
    showNotification: vi.fn(),
}));

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: {
        template: '<a><slot /></a>',
        props: ['href'],
    },
    usePage: () => ({ props: pageProps }),
    router: { visit: vi.fn(), get: vi.fn() },
}));

vi.mock('@/modules/MaterialManager/ui/composables/useMaterials', async () => {
    const { ref } = await import('vue');
    const noop = vi.fn();
    return {
        useMaterials: () => ({
            filters: ref({ search: '', state: '', category_id: null, author: null, sort: 'id', direction: 'desc' }),
            perPage: ref(10),
            selectedMaterials: ref<number[]>([]),
            allSelected: ref(false),
            notification: ref({ show: false, message: '', type: 'success' }),
            formatDate: (date: string) => date,
            toggleSelect: noop,
            selectAll: noop,
            applyFilters: noop,
            debounceSearch: noop,
            resetFilters: noop,
            changePerPage: noop,
            prevPage: noop,
            nextPage: noop,
            moveToTrash: noop,
            publishSelected: noop,
            unpublishSelected: noop,
            editSelected: noop,
            showNotification,
            toggleHomepage: noop,
            toggleSort: noop,
            items: ref([]),
        }),
    };
});

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

function mountIndex() {
    return mount(Index, {
        props: {
            user: { id: 1, name: 'Admin' },
            title: 'Менеджер материалов',
            materials: {
                data: [],
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0,
                from: null,
                to: null,
            },
            categories: [],
            authors: [],
        },
    });
}

describe('Index.vue (flash-тост после «Сохранить и закрыть»)', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        pageProps.flash = undefined;
    });

    it('показывает тост ровно один раз при flash.success в props', () => {
        pageProps.flash = { success: 'Материал обновлён' };

        mountIndex();

        expect(showNotification).toHaveBeenCalledTimes(1);
        expect(showNotification).toHaveBeenCalledWith('Материал обновлён', 'success');
    });

    it('не показывает тост без flash (F5 без flash-данных)', () => {
        pageProps.flash = undefined;

        mountIndex();

        expect(showNotification).not.toHaveBeenCalled();
    });

    it('не показывает тост при пустом flash.success', () => {
        pageProps.flash = { success: null as unknown as string };

        mountIndex();

        expect(showNotification).not.toHaveBeenCalled();
    });

    it('показывает error-тост при flash.error', () => {
        pageProps.flash = { error: 'Ошибка удаления' };

        mountIndex();

        expect(showNotification).toHaveBeenCalledTimes(1);
        expect(showNotification).toHaveBeenCalledWith('Ошибка удаления', 'error');
    });
});
