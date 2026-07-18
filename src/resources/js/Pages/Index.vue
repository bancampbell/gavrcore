<template>
    <LayoutSwitcher>
        <Head>
            <title>{{ meta?.title || title }}</title>
            <meta name="description" :content="meta?.description || description || siteDescription" />
            <meta name="keywords" :content="meta?.keywords || keywords || siteKeywords" />

            <!-- Open Graph -->
            <meta property="og:title" :content="meta?.og_title || meta?.title || title" />
            <meta property="og:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
            <meta property="og:url" :content="meta?.canonical || window.location.href" />
            <meta property="og:type" :content="meta?.og_type || 'website'" />
            <meta property="og:image" :content="meta?.og_image || ''" />
            <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

            <!-- Twitter Cards -->
            <meta name="twitter:card" :content="meta?.twitter_card || 'summary_large_image'" />
            <meta name="twitter:title" :content="meta?.og_title || meta?.title || title" />
            <meta name="twitter:description" :content="meta?.og_description || meta?.description || description || siteDescription" />
            <meta name="twitter:image" :content="meta?.og_image || ''" />

            <!-- Canonical -->
            <link rel="canonical" :href="meta?.canonical || window.location.href" />
        </Head>

        <div class="container mx-auto px-4">
            <!-- Хлебные крошки -->
            <Breadcrumbs :items="breadcrumbs || []" />

            <div class="space-y-6">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow p-8 text-white">
                    <h1 class="text-3xl font-bold">Добро пожаловать в GavrCore CMS</h1>
                    <p class="mt-2 opacity-90">{{ siteDescription || 'Управляйте своим контентом легко и быстро.' }}</p>
                </div>

                <div v-if="homepageMaterial" class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <div v-if="showDate || showCategory" class="flex items-center justify-between mb-2">
                            <span v-if="showDate" class="text-xs text-gray-500">{{ formatDate(homepageMaterial.created_at) }}</span>
                            <span v-if="showCategory" class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                {{ homepageMaterial.category?.name || 'Без категории' }}
                            </span>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 mb-4">
                            {{ homepageMaterial.title }}
                        </h1>

                        <ShortcodeRenderer
                            :content="homepageMaterial.content"
                            :forms="forms"
                        />

                        <div v-if="showAuthor || showViews" class="mt-6 flex items-center justify-between text-sm text-gray-500">
                            <div class="flex items-center space-x-4">
                                <span v-if="showAuthor">Автор: {{ homepageMaterial.user?.name }}</span>
                                <span v-if="showViews">Просмотров: {{ homepageMaterial.views }}</span>
                            </div>
                            <Link :href="`/material/${homepageMaterial.slug}`" class="text-indigo-600 hover:text-indigo-800">
                                Читать полностью →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                    <p>Нет материала, отмеченного для показа на главной.</p>
                    <p class="text-sm mt-2">Выберите материал в админке и отметьте "Показывать на главной".</p>
                </div>
            </div>
        </div>
    </LayoutSwitcher>
</template>

<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import ShortcodeRenderer from '@/components/shared/ShortcodeRenderer.vue';
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    homepageMaterial: Object,
    title: String,
    description: String,
    keywords: String,
    forms: {
        type: Object,
        default: () => ({}),
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

const useGlobal = props.homepageMaterial?.use_global_settings ?? true;

const showDate = useGlobal
    ? (appSettings.show_date ?? true)
    : (props.homepageMaterial?.show_date ?? true);

const showAuthor = useGlobal
    ? (appSettings.show_author ?? true)
    : (props.homepageMaterial?.show_author ?? true);

const showCategory = useGlobal
    ? (appSettings.show_category ?? true)
    : (props.homepageMaterial?.show_category ?? true);

const showViews = useGlobal
    ? (appSettings.show_views ?? true)
    : (props.homepageMaterial?.show_views ?? true);

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};
</script>
