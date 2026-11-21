import { describe, it, expect, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import Home from '@/modules/Site/ui/Home.vue';
import CategoryShow from '@/modules/Site/ui/Category/Show.vue';

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

// Регрессия TECHDEBT 79: страница категории через общий SeoHead отдаёт
// twitter:* и og:image (раньше блок <Head> был усечённой копией без них).
describe('Site/Category — twitter:* и og:image через shared SeoHead (TECHDEBT 79)', () => {
    it('рендерит twitter:card и og:image', () => {
        const wrapper = mount(CategoryShow, {
            props: {
                category: { name: 'Новости', description: 'Лента новостей' },
            },
        });

        const twitterCard = wrapper.find('meta[name="twitter:card"]');
        expect(twitterCard.exists()).toBe(true);
        expect(twitterCard.attributes('content')).toBe('summary_large_image');

        expect(wrapper.find('meta[name="twitter:title"]').exists()).toBe(true);
        expect(wrapper.find('meta[name="twitter:description"]').exists()).toBe(true);
        expect(wrapper.find('meta[name="twitter:image"]').exists()).toBe(true);
        expect(wrapper.find('meta[property="og:image"]').exists()).toBe(true);
    });
});

// Факт-чек TECHDEBT 71/79: блок <Head> продублирован был на четырёх публичных
// страницах — все четверо ходят через shared SeoHead, инлайн-меты в страницах нет,
// currentUrl собирается внутри SeoHead (эталон 416c579).
describe('TECHDEBT 79 — все четыре публичные страницы на shared SeoHead', () => {
    const modulesDir = resolve(process.cwd(), 'resources/js/modules');
    const pages = [
        'Site/ui/Home.vue',
        'MaterialManager/ui/Show.vue',
        'Site/ui/Category/Show.vue',
        'Site/ui/Search/Index.vue',
    ];

    it.each(pages)('%s использует SeoHead, инлайн-<Head> и currentUrl отсутствуют', (rel) => {
        const src = readFileSync(`${modulesDir}/${rel}`, 'utf-8');

        expect(src).toContain('<SeoHead');
        expect(src).not.toContain('<Head>');
        expect(src).not.toContain('window.location.origin + page.url');
        expect(src).not.toContain('og:site_name');
    });

    it('SeoHead собирает currentUrl из window.location.origin + page.url', () => {
        const src = readFileSync(
            resolve(process.cwd(), 'resources/js/components/shared/SeoHead.vue'),
            'utf-8',
        );

        expect(src).toContain('const currentUrl = window.location.origin + page.url;');
        expect(src).not.toContain('window.location.href');
    });
});
