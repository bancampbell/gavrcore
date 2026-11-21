import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import Home from '@/modules/Site/ui/Home.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { template: '<div><slot /></div>' },
    Link: {
        template: '<a><slot /></a>',
        props: ['href'],
    },
    usePage: () => ({ props: { appSettings: {} }, url: '/demo/path?x=1' }),
    router: { visit: vi.fn(), get: vi.fn() },
}));

vi.mock('@/layouts/LayoutSwitcher.vue', () => ({
    default: {
        template: '<div><slot /></div>',
    },
}));

vi.mock('@/components/shared/ShortcodeRenderer.vue', () => ({
    default: {
        template: '<div class="shortcode-renderer" />',
        props: ['content', 'forms'],
    },
}));

vi.mock('@/modules/Site/ui/components/Breadcrumbs.vue', () => ({
    default: {
        template: '<nav class="breadcrumbs" />',
        props: ['items'],
    },
}));

describe('Site/Home — SEO-мета из page.url (TECHDEBT 71)', () => {
    it('рендерит og:url и canonical из window.location.origin + page.url без падения', () => {
        const wrapper = mount(Home, {
            props: { title: 'Главная' },
        });

        const expected = window.location.origin + '/demo/path?x=1';

        const ogUrl = wrapper.find('meta[property="og:url"]');
        expect(ogUrl.exists()).toBe(true);
        expect(ogUrl.attributes('content')).toBe(expected);

        const canonical = wrapper.find('link[rel="canonical"]');
        expect(canonical.exists()).toBe(true);
        expect(canonical.attributes('href')).toBe(expected);
    });

    it('meta.canonical имеет приоритет над текущим URL', () => {
        const wrapper = mount(Home, {
            props: {
                title: 'Главная',
                meta: { canonical: 'https://example.com/custom-canonical' },
            },
        });

        expect(wrapper.find('meta[property="og:url"]').attributes('content'))
            .toBe('https://example.com/custom-canonical');
        expect(wrapper.find('link[rel="canonical"]').attributes('href'))
            .toBe('https://example.com/custom-canonical');
    });
});

// Факт-чек TECHDEBT 71: баг был одинаковым во всех четырёх публичных страницах —
// фиксим представителя (Home) поведенческим тестом выше, остальные три контролируем
// паттерном: ни одного window.location.href в шаблоне, URL собирается в script setup.
describe('TECHDEBT 71 — паттерн фикса идентичен в остальных публичных страницах', () => {
    const modulesDir = resolve(process.cwd(), 'resources/js/modules');
    const siblings = [
        'MaterialManager/ui/Show.vue',
        'Site/ui/Category/Show.vue',
        'Site/ui/Search/Index.vue',
    ];

    it.each(siblings)('%s собирает URL в script setup, не в шаблоне', (rel) => {
        const src = readFileSync(`${modulesDir}/${rel}`, 'utf-8');

        expect(src).not.toContain('window.location.href');
        expect(src).toContain('const currentUrl = window.location.origin + page.url;');
        expect(src.match(/meta\?\.canonical \|\| currentUrl/g)).toHaveLength(2);
    });
});
