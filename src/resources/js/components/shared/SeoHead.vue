<template>
    <Head>
        <title>{{ meta?.title || title }}</title>
        <meta name="description" :content="meta?.description || description || siteDescription" />
        <meta name="keywords" :content="meta?.keywords || keywords || siteKeywords" />

        <!-- Open Graph -->
        <meta property="og:title" :content="meta?.og_title || meta?.title || title" />
        <meta property="og:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
        <meta property="og:url" :content="canonicalUrl" />
        <meta property="og:type" :content="meta?.og_type || ogType" />
        <meta property="og:image" :content="meta?.og_image || image" />
        <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

        <!-- Twitter Cards -->
        <meta name="twitter:card" :content="meta?.twitter_card || 'summary_large_image'" />
        <meta name="twitter:title" :content="meta?.og_title || meta?.title || title" />
        <meta name="twitter:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
        <meta name="twitter:image" :content="meta?.og_image || image" />

        <!-- Canonical -->
        <link rel="canonical" :href="canonicalUrl" />
    </Head>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    keywords: {
        type: String,
        default: '',
    },
    image: {
        type: String,
        default: '',
    },
    canonical: {
        type: String,
        default: '',
    },
    ogType: {
        type: String,
        default: 'website',
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
});

const siteDescription = appSettings.site_description || '';
const siteKeywords = appSettings.seo_keywords || '';

// Эталон п.71 (416c579): URL текущей страницы собирается в script setup,
// не в шаблоне (render-proxy Vue 3.5 не пробрасывает window в шаблон).
const currentUrl = window.location.origin + page.url;
const canonicalUrl = computed(() => props.meta?.canonical || props.canonical || currentUrl);
</script>
