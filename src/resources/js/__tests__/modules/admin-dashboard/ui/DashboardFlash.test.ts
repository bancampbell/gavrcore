import { describe, it, expect, vi, beforeEach } from 'vitest';
import { h } from 'vue';
import { mount } from '@vue/test-utils';
import Dashboard from '@/modules/AdminDashboard/ui/Dashboard.vue';

const { pageProps, toastRenders } = vi.hoisted(() => ({
    pageProps: { flash: undefined as { success?: string; error?: string } | undefined },
    toastRenders: [] as Array<{ show: boolean; message: string; type: string }>,
}));

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    usePage: () => ({ props: pageProps }),
    router: { get: vi.fn() },
}));

vi.mock('@/layouts/AdminLayout.vue', () => ({
    default: {
        template: '<div class="admin-layout"><slot /></div>',
        props: ['user'],
    },
}));

vi.mock('@/components/shared/Toast.vue', () => ({
    default: {
        name: 'Toast',
        props: ['show', 'message', 'type'],
        setup(props: { show: boolean; message: string; type: string }) {
            return () => {
                toastRenders.push({ show: props.show, message: props.message, type: props.type });
                return h('div', { class: 'toast' });
            };
        },
    },
}));

function shownToasts() {
    return toastRenders.filter(render => render.show && render.message !== '');
}

function mountDashboard() {
    return mount(Dashboard, {
        props: {
            user: { id: 1, name: 'Admin' },
            title: 'Панель управления',
            stats: { materials: 0, categories: 0, users: 0, views: 0 },
            activities: {
                data: [],
                current_page: 1,
                last_page: 1,
                from: null,
                to: null,
                total: 0,
            },
        },
    });
}

describe('Dashboard.vue (flash-тост после «Сохранить и закрыть» настроек)', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        toastRenders.length = 0;
        pageProps.flash = undefined;
    });

    it('показывает тост ровно один раз при flash.success в props', async () => {
        pageProps.flash = { success: 'Настройки сохранены' };

        mountDashboard();
        await new Promise(resolve => setTimeout(resolve, 0));

        const shown = shownToasts();
        expect(shown).toHaveLength(1);
        expect(shown[0]).toMatchObject({ message: 'Настройки сохранены', type: 'success' });
    });

    it('не показывает тост без flash (F5 без flash-данных)', () => {
        pageProps.flash = undefined;

        mountDashboard();

        expect(shownToasts()).toHaveLength(0);
    });

    it('не показывает тост при пустом flash.success', () => {
        pageProps.flash = { success: undefined };

        mountDashboard();

        expect(shownToasts()).toHaveLength(0);
    });
});
