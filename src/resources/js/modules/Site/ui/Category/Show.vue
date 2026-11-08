<template>
    <LayoutSwitcher>
        <Head>
            <title>{{ meta?.title || category?.name || 'Категория' }}</title>
            <meta name="description" :content="meta?.description || category?.description || siteDescription" />
            <meta name="keywords" :content="meta?.keywords || siteKeywords" />

            <!-- Open Graph -->
            <meta property="og:title" :content="meta?.og_title || meta?.title || category?.name || ''" />
            <meta property="og:description" :content="meta?.og_description || meta?.description || category?.description || siteDescription" />
            <meta property="og:url" :content="meta?.canonical || window.location.href" />
            <meta property="og:type" :content="meta?.og_type || 'website'" />
            <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

            <!-- Canonical -->
            <link rel="canonical" :href="meta?.canonical || window.location.href" />
        </Head>

        <div class="container mx-auto px-4">
            <Breadcrumbs :items="breadcrumbs || []" />

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-100">
                    <h1 class="text-3xl font-bold text-gray-900">{{ category?.name }}</h1>
                    <p v-if="category?.description" class="mt-2 text-gray-600">{{ category.description }}</p>
                </div>

                <template v-if="materialsList.length">
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="material in materialsList"
                            :key="material.id"
                            class="px-6 py-3 hover:bg-blue-50 transition-colors"
                        >
                            <Link :href="materialUrl(material)" class="text-gray-900 hover:text-indigo-600">
                                {{ material.title }}
                            </Link>
                        </div>
                    </div>
                </template>
                <div v-else class="p-8 text-center text-gray-500">
                    <p>В этой категории пока нет материалов.</p>
                </div>
            </div>

            <div v-if="materialsList.length && materials.last_page > 1" class="flex items-center justify-between bg-white rounded-lg shadow px-6 py-4 mt-4">
                <Link
                    v-if="materials.prev_page_url"
                    :href="materials.prev_page_url"
                    class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800"
                >
                    ← Назад
                </Link>
                <span v-else class="text-gray-300">← Назад</span>

                <span class="text-sm text-gray-500">Страница {{ materials.current_page }} из {{ materials.last_page }}</span>

                <Link
                    v-if="materials.next_page_url"
                    :href="materials.next_page_url"
                    class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800"
                >
                    Вперёд →
                </Link>
                <span v-else class="text-gray-300">Вперёд →</span>
            </div>
        </div>
    </LayoutSwitcher>
</template>

<script setup>
import {Link, Head, usePage} from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    category: {
        type: Object,
        default: () => ({}),
    },
    materials: {
        type: Object,
        default: () => ({data: []}),
    },
    meta: {
        type: Object,
        default: () => ({}),
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const siteDescription = appSettings.site_description || '';
const siteKeywords = appSettings.seo_keywords || '';

const materialsList = props.materials?.data || [];

const materialUrl = (material) => {
    const slug = material.slug || material.alias;
    return material.category ? `/${material.category.slug}/${slug}` : `/${slug}`;
};
</script>
