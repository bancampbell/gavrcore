import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import FormRenderer from '@/modules/FormBuilder/ui/components/FormRenderer.vue';
import axios from 'axios';

vi.mock('axios', () => ({
    default: { get: vi.fn(), post: vi.fn() },
}));

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: { auth: { user: null } } }),
}));

const captchaMocks = vi.hoisted(() => ({ reset: vi.fn() }));

vi.mock('@/components/shared/CaptchaWidget.vue', () => ({
    default: {
        name: 'CaptchaWidget',
        template: '<div class="captcha-stub" />',
        emits: ['update:token'],
        methods: { reset: captchaMocks.reset },
    },
}));

const flush = () => new Promise(resolve => setTimeout(resolve, 0));

const makeForm = (overrides: Record<string, unknown> = {}) => ({
    id: 2,
    title: 'Тестовая форма',
    description: '',
    fields: [
        { id: 'f1', type: 'text', name: 'ФИО', label: 'ФИО', required: true },
        { id: 'f2', type: 'phone', name: 'phone', label: 'Телефон', required: true },
        { id: 'f3', type: 'email', name: 'email', label: 'Email', required: true },
    ],
    captcha_enabled: false,
    ...overrides,
});

const validationError = (errors: Record<string, string[]>) => ({
    response: { status: 422, data: { message: 'Ошибка валидации', errors } },
});

const mountRenderer = async (formConfig: unknown): Promise<VueWrapper> => {
    vi.mocked(axios.get).mockResolvedValue({ data: formConfig });
    const wrapper = mount(FormRenderer, { props: { formId: 2 } });
    await flush();
    await wrapper.vm.$nextTick();
    return wrapper;
};

const submit = async (wrapper: VueWrapper) => {
    await wrapper.find('form').trigger('submit');
    await flush();
    await wrapper.vm.$nextTick();
};

describe('FormRenderer.vue (полевые ошибки 422)', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('при 422 подсвечивает поля и показывает тексты ошибок под ними, баннер не дублирует', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue(validationError({
            phone: ['Укажите контактный телефон'],
            email: ['Некорректный адрес email'],
        }));

        await submit(wrapper);

        // Оба поля подсвечены, ФИО — нет.
        const errorGroups = wrapper.findAll('.form-group.has-error');
        expect(errorGroups).toHaveLength(2);

        // Тексты ошибок в DOM под соответствующими полями.
        const fieldErrors = wrapper.findAll('.form-field-error');
        expect(fieldErrors).toHaveLength(2);
        const phoneGroup = wrapper.find('input#phone').element.closest('.form-group')!;
        const emailGroup = wrapper.find('input#email').element.closest('.form-group')!;
        expect(phoneGroup.querySelector('.form-field-error')?.textContent).toContain('Укажите контактный телефон');
        expect(emailGroup.querySelector('.form-field-error')?.textContent).toContain('Некорректный адрес email');

        // Баннер не дублирует ошибки найденных полей.
        expect(wrapper.find('.form-error').exists()).toBe(false);
        expect(wrapper.find('input#ФИО').element.closest('.form-group')!.classList.contains('has-error')).toBe(false);
    });

    it('повторный ввод в поле снимает его ошибку, соседние сохраняются', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue(validationError({
            phone: ['Укажите контактный телефон'],
            email: ['Некорректный адрес email'],
        }));

        await submit(wrapper);
        expect(wrapper.findAll('.form-field-error')).toHaveLength(2);

        await wrapper.find('input#phone').setValue('89001234567');
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('.form-field-error')).toHaveLength(1);
        expect(wrapper.text()).toContain('Некорректный адрес email');
        expect(wrapper.text()).not.toContain('Укажите контактный телефон');
        expect(wrapper.find('input#email').element.closest('.form-group')!.classList.contains('has-error')).toBe(true);
    });

    it('при 422 показывает ВСЕ сообщения поля, а не только первое', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue(validationError({
            email: [
                'Поле «Email» обязательно для заполнения.',
                'Поле «Email» должно содержать корректный email-адрес.',
            ],
        }));

        await submit(wrapper);

        const emailGroup = wrapper.find('input#email').element.closest('.form-group')!;
        const errorParas = emailGroup.querySelectorAll('.form-field-error');
        expect(errorParas).toHaveLength(2);
        expect(errorParas[0].textContent).toContain('обязательно для заполнения');
        expect(errorParas[1].textContent).toContain('корректный email-адрес');
    });

    it('несколько полей с ошибками подсвечены и показаны одновременно', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue(validationError({
            'ФИО': ['Поле «ФИО» обязательно для заполнения.', 'Вторая ошибка ФИО'],
            phone: ['Поле «Телефон» обязательно для заполнения.'],
            email: ['Поле «Email» должно содержать корректный email-адрес.'],
        }));

        await submit(wrapper);

        // Все три поля подсвечены одним проходом.
        expect(wrapper.findAll('.form-group.has-error')).toHaveLength(3);
        // Все четыре сообщения в DOM одновременно (2 под ФИО + 1 + 1).
        expect(wrapper.findAll('.form-field-error')).toHaveLength(4);
        expect(wrapper.text()).toContain('Вторая ошибка ФИО');
        expect(wrapper.text()).toContain('корректный email-адрес');
        expect(wrapper.text()).toContain('обязательно для заполнения');
        expect(wrapper.find('.form-error').exists()).toBe(false);
    });

    it('ошибки без поля в разметке (captcha_token и пр.) уходят в generic-баннер', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue(validationError({
            captcha_token: ['Проверка капчи не пройдена. Обновите страницу и попробуйте ещё раз.'],
            unknown_key: ['Что-то пошло не так'],
        }));

        await submit(wrapper);

        expect(wrapper.findAll('.form-field-error')).toHaveLength(0);
        const banner = wrapper.find('.form-error');
        expect(banner.exists()).toBe(true);
        expect(banner.text()).toContain('Проверка капчи не пройдена');
        expect(banner.text()).toContain('Что-то пошло не так');
    });

    it('сетевой сбой без полевых ошибок показывает fallback в баннере', async () => {
        const wrapper = await mountRenderer(makeForm());
        vi.mocked(axios.post).mockRejectedValue({});

        await submit(wrapper);

        expect(wrapper.find('.form-error').text()).toBe('Ошибка отправки формы');
    });

    it('required-поле, скрытое условием, не попадает в payload submit', async () => {
        const wrapper = await mountRenderer(makeForm({
            fields: [
                {
                    name: 'category', label: 'Категория', type: 'radio', required: true,
                    options: ['reception', 'consult'],
                },
                {
                    name: 'details', label: 'Детали', type: 'textarea', required: true,
                    conditions: [{ field: 'category', operator: 'equals', value: 'consult' }],
                },
            ],
        }));
        vi.mocked(axios.post).mockResolvedValue({ data: { message: 'Форма успешно отправлена' } });

        // Выбираем категорию, при которой details скрыто.
        await wrapper.find('input[type="radio"][value="reception"]').setValue(true);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('textarea#details').exists()).toBe(false);

        await submit(wrapper);

        expect(wrapper.find('.form-error').exists()).toBe(false);
        const sent = vi.mocked(axios.post).mock.calls[0][1] as FormData;
        expect(sent.get('category')).toBe('reception');
        expect(sent.get('details')).toBeNull();
    });

    it('после 422 сбрасывает токен и виджет капчи (одноразовый токен)', async () => {
        const wrapper = await mountRenderer(makeForm({
            captcha_enabled: true,
            captcha: { driver: 'turnstile', site_key: 'test-key' },
        }));
        vi.mocked(axios.post).mockRejectedValue(validationError({
            phone: ['Укажите контактный телефон'],
        }));

        wrapper.findComponent({ name: 'CaptchaWidget' }).vm.$emit('update:token', 'first-token');
        await wrapper.vm.$nextTick();

        await submit(wrapper);

        expect(captchaMocks.reset).toHaveBeenCalledTimes(1);

        // Повторный submit уходит без старого токена, пока виджет не выдал новый.
        await submit(wrapper);
        const secondSent = vi.mocked(axios.post).mock.calls[1][1] as FormData;
        expect(secondSent.get('captcha_token')).toBeNull();
        expect(captchaMocks.reset).toHaveBeenCalledTimes(2);
    });
});
