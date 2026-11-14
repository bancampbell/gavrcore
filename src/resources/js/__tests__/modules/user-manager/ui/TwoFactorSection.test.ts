import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import TwoFactorSection from '@/modules/UserManager/ui/Profile/TwoFactorSection.vue';

vi.mock('axios', () => ({
    default: { post: vi.fn() },
}));

vi.mock('qrcode', () => ({
    default: { toDataURL: vi.fn(() => Promise.resolve('data:image/png;base64,FAKEQR')) },
}));

import axios from 'axios';

const RECOVERY_CODES = [
    'A1B2-C3D4-E5F6-A7B8',
    'B2C3-D4E5-F6A7-B8C9',
    'C3D4-E5F6-A7B8-C9D0',
    'D4E5-F6A7-B8C9-D0E1',
    'E5F6-A7B8-C9D0-E1F2',
    'F6A7-B8C9-D0E1-F2A3',
    'A7B8-C9D0-E1F2-A3B4',
    'B8C9-D0E1-F2A3-B4C5',
];

const flush = () => new Promise(resolve => setTimeout(resolve, 0));

const mountSection = (enabled = false) => mount(TwoFactorSection, {
    props: { enabled },
});

const mockPostByUrl = (impl: (url: string) => Promise<unknown>) => {
    vi.mocked(axios.post).mockImplementation((url: string) => impl(url));
};

describe('TwoFactorSection.vue (секция 2FA в профиле)', () => {
    beforeEach(() => {
        vi.clearAllMocks();
        Object.assign(navigator, {
            clipboard: { writeText: vi.fn().mockResolvedValue(undefined) },
        });
    });

    it('выключена: показывает статус и кнопку «Включить»', () => {
        const wrapper = mountSection(false);

        expect(wrapper.text()).toContain('Выключена');
        expect(wrapper.text()).toContain('Включить');
        expect(wrapper.find('input[type="password"]').exists()).toBe(false);
    });

    it('включена: показывает статус и форму отключения с паролем', () => {
        const wrapper = mountSection(true);

        expect(wrapper.text()).toContain('Включена');
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Отключить');
    });

    it('поток включения: enable → QR → confirm → 8 резервных кодов → Готово', async () => {
        mockPostByUrl(async (url) => {
            if (url === '/profile/2fa/enable') {
                return { data: { success: true, data: { otpauth_url: 'otpauth://totp/test' } } };
            }
            if (url === '/profile/2fa/confirm') {
                return { data: { success: true, data: { recovery_codes: RECOVERY_CODES } } };
            }

            throw new Error(`unexpected ${url}`);
        });

        const wrapper = mountSection(false);

        await wrapper.find('button:not([type])').trigger('click');
        await flush();

        expect(axios.post).toHaveBeenCalledWith('/profile/2fa/enable');
        const qr = wrapper.find('img[alt="QR-код для настройки двухфакторной аутентификации"]');
        expect(qr.exists()).toBe(true);
        expect(qr.attributes('src')).toBe('data:image/png;base64,FAKEQR');

        await wrapper.find('input').setValue('123456');
        const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Подтвердить'))!;
        await confirmButton.trigger('click');
        await flush();

        expect(axios.post).toHaveBeenCalledWith('/profile/2fa/confirm', { code: '123456' });
        expect(wrapper.text()).toContain('они больше не показываются');
        expect(wrapper.findAll('li')).toHaveLength(8);
        expect(wrapper.text()).toContain(RECOVERY_CODES[0]);

        const doneButton = wrapper.findAll('button').find(b => b.text().includes('Готово'))!;
        await doneButton.trigger('click');

        expect(wrapper.text()).toContain('Включена');
    });

    it('confirm с 422: показывает ошибку под полем кода', async () => {
        mockPostByUrl(async (url) => {
            if (url === '/profile/2fa/enable') {
                return { data: { success: true, data: { otpauth_url: 'otpauth://totp/test' } } };
            }
            if (url === '/profile/2fa/confirm') {
                throw {
                    response: {
                        status: 422,
                        data: { message: 'Неверный код подтверждения', errors: { code: ['Неверный код подтверждения'] } },
                    },
                };
            }

            throw new Error(`unexpected ${url}`);
        });

        const wrapper = mountSection(false);

        await wrapper.find('button:not([type])').trigger('click');
        await flush();

        await wrapper.find('input').setValue('000000');
        const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Подтвердить'))!;
        await confirmButton.trigger('click');
        await flush();

        expect(wrapper.text()).toContain('Неверный код подтверждения');
    });

    it('копирование резервных кодов', async () => {
        mockPostByUrl(async (url) => {
            if (url === '/profile/2fa/enable') {
                return { data: { success: true, data: { otpauth_url: 'otpauth://totp/test' } } };
            }

            return { data: { success: true, data: { recovery_codes: RECOVERY_CODES } } };
        });

        const wrapper = mountSection(false);

        await wrapper.find('button:not([type])').trigger('click');
        await flush();
        await wrapper.find('input').setValue('123456');
        const confirmButton = wrapper.findAll('button').find(b => b.text().includes('Подтвердить'))!;
        await confirmButton.trigger('click');
        await flush();

        const copyButton = wrapper.findAll('button').find(b => b.text().includes('Скопировать коды'))!;
        await copyButton.trigger('click');
        await flush();

        expect(navigator.clipboard.writeText).toHaveBeenCalledWith(RECOVERY_CODES.join('\n'));
        expect(wrapper.text()).toContain('Скопировано');
    });

    it('отключение: disable с паролем → статус «Выключена»', async () => {
        mockPostByUrl(async (url) => {
            if (url === '/profile/2fa/disable') {
                return { data: { success: true, message: 'Двухфакторная аутентификация отключена' } };
            }

            throw new Error(`unexpected ${url}`);
        });

        const wrapper = mountSection(true);

        await wrapper.find('input[type="password"]').setValue('secret-password');
        const disableButton = wrapper.findAll('button').find(b => b.text().includes('Отключить'))!;
        await disableButton.trigger('click');
        await flush();

        expect(axios.post).toHaveBeenCalledWith('/profile/2fa/disable', { current_password: 'secret-password' });
        expect(wrapper.text()).toContain('Выключена');
        expect(wrapper.text()).toContain('Включить');
    });

    it('disable с 422: показывает ошибку пароля под полем', async () => {
        mockPostByUrl(async () => {
            throw {
                response: {
                    status: 422,
                    data: { message: 'Текущий пароль неверен', errors: { current_password: ['Текущий пароль неверен'] } },
                },
            };
        });

        const wrapper = mountSection(true);

        await wrapper.find('input[type="password"]').setValue('wrong');
        const disableButton = wrapper.findAll('button').find(b => b.text().includes('Отключить'))!;
        await disableButton.trigger('click');
        await flush();

        expect(wrapper.text()).toContain('Текущий пароль неверен');
    });
});
