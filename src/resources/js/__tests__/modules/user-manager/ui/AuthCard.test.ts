import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import AuthCard from '@/modules/UserManager/ui/Auth/AuthCard.vue';

describe('AuthCard.vue (карточка auth-страниц)', () => {
    it('рендерит title и subtitle при передаче', () => {
        const wrapper = mount(AuthCard, {
            props: { title: 'GavrCore CMS', subtitle: 'Вход в аккаунт' },
        });

        expect(wrapper.find('h1').text()).toBe('GavrCore CMS');
        expect(wrapper.find('p').text()).toBe('Вход в аккаунт');
    });

    it('не рендерит блок заголовка, если title и subtitle не переданы', () => {
        const wrapper = mount(AuthCard, {
            slots: { default: '<form>form-content</form>' },
        });

        expect(wrapper.find('h1').exists()).toBe(false);
        expect(wrapper.find('.bg-gradient-to-r').exists()).toBe(false);
    });

    it('не рендерит пустой подзаголовок при переданном только title', () => {
        const wrapper = mount(AuthCard, {
            props: { title: 'GavrCore CMS' },
        });

        expect(wrapper.find('h1').exists()).toBe(true);
        expect(wrapper.find('p').exists()).toBe(false);
    });

    it('рендерит slot-контент', () => {
        const wrapper = mount(AuthCard, {
            props: { title: 'GavrCore CMS', subtitle: 'Вход в аккаунт' },
            slots: { default: '<form>form-content</form>' },
        });

        expect(wrapper.find('form').exists()).toBe(true);
        expect(wrapper.text()).toContain('form-content');
    });
});
