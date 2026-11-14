import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import TwoFactorChallenge from '@/modules/UserManager/ui/Auth/TwoFactorChallenge.vue';
import AuthCard from '@/modules/UserManager/ui/Auth/AuthCard.vue';

const routerMocks = vi.hoisted(() => ({
    post: vi.fn(),
    visit: vi.fn(),
}));

vi.mock('@inertiajs/vue3', () => ({
    router: routerMocks,
}));

vi.mock('@/layouts/LayoutSwitcher.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

const flush = () => new Promise(resolve => setTimeout(resolve, 0));

const mountPage = () => mount(TwoFactorChallenge);

describe('TwoFactorChallenge.vue (страница ввода кода 2FA)', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('рендерит подзаголовок, подсказку и поле кода', () => {
        const wrapper = mountPage();

        // AuthCard title/subtitle держит в пропсах (в DOM не рендерит)
        const card = wrapper.getComponent(AuthCard);
        expect(card.props('title')).toBe('GavrCore CMS');
        expect(card.props('subtitle')).toBe('Двухфакторная аутентификация');
        expect(wrapper.text()).toContain('код из приложения-аутентификатора или резервный код');
        expect(wrapper.find('input').exists()).toBe(true);
        expect(wrapper.find('button[type="submit"]').text()).toContain('Подтвердить');
    });

    it('отправляет POST /2fa/verify с введённым кодом', async () => {
        routerMocks.post.mockResolvedValue(undefined);
        const wrapper = mountPage();

        await wrapper.find('input').setValue('123456');
        await wrapper.find('form').trigger('submit');
        await flush();

        expect(routerMocks.post).toHaveBeenCalledWith(
            '/2fa/verify',
            { code: '123456' },
            expect.objectContaining({ preserveState: true }),
        );
    });

    it('показывает ошибку валидации под полем и не сбрасывает форму', async () => {
        routerMocks.post.mockImplementation((url, data, options) => {
            options.onError({ code: 'Неверный код' });

            return Promise.resolve();
        });
        const wrapper = mountPage();

        await wrapper.find('input').setValue('000000');
        await wrapper.find('form').trigger('submit');
        await flush();

        expect(wrapper.text()).toContain('Неверный код');
        expect(wrapper.find('input').element.value).toBe('000000');
    });

    it('при 403 (нет сессионной метки) уводит на /login', async () => {
        routerMocks.post.mockRejectedValue({ response: { status: 403 } });
        const wrapper = mountPage();

        await wrapper.find('input').setValue('123456');
        await wrapper.find('form').trigger('submit');
        await flush();

        expect(routerMocks.visit).toHaveBeenCalledWith('/login');
    });
});
