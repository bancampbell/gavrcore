<template>
    <LayoutSwitcher>
        <Head>
            <title>{{ title || 'Поиск' }}</title>
            <meta name="description" :content="description || siteDescription" />
            <meta name="keywords" :content="keywords || siteKeywords" />

            <!-- Open Graph -->
            <meta property="og:title" :content="meta?.og_title || title || 'Поиск'" />
            <meta property="og:description" :content="meta?.og_description || description || siteDescription" />
            <meta property="og:url" :content="meta?.canonical || window.location.href" />
            <meta property="og:type" content="website" />
            <meta property="og:site_name" :content="appSettings?.site_name || 'GavrCore CMS'" />

            <!-- Canonical -->
            <link rel="canonical" :href="meta?.canonical || window.location.href" />
        </Head>

        <div class="container mx-auto px-4">
            <Breadcrumbs :items="breadcrumbs || []" />

            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ title || 'Поиск' }}</h1>

                <form :action="materials.path || '/search'" method="get" class="flex gap-2">
                    <input
                        type="text"
                        name="q"
                        :value="search || ''"
                        placeholder="Введите поисковый запрос…"
                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        Найти
                    </button>
                </form>
            </div>

            <div v-if="materialsList.length" class="space-y-4">
                <div
                    v-for="material in materialsList"
                    :key="material.id"
                    class="bg-white rounded-lg shadow overflow-hidden"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-500">{{ formatDate(material.published_at || material.created_at) }}</span>
                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                {{ material.category?.name || 'Без категории' }}
                            </span>
                        </div>

                        <h2 class="text-xl font-bold text-gray-900 mb-2">
                            <Link :href="`/material/${material.slug || material.alias}`" class="hover:text-indigo-600">
                                {{ material.title }}
                            </Link>
                        </h2>

                        <p v-if="excerpt(material.content)" class="text-gray-600 mb-3">{{ excerpt(material.content) }}</p>

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <span v-if="material.user?.name">Автор: {{ material.user.name }}</span>
                            <span v-if="material.views !== undefined">Просмотров: {{ material.views }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="materials.last_page > 1" class="flex items-center justify-between bg-white rounded-lg shadow px-6 py-4">
                    <Link
                        v-if="materials.current_page > 1"
                        :href="pageUrl(materials.current_page - 1)"
                        class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800"
                    >
                        ← Назад
                    </Link>
                    <span v-else class="text-gray-300">← Назад</span>

                    <span class="text-sm text-gray-500">Страница {{ materials.current_page }} из {{ materials.last_page }}</span>

                    <Link
                        v-if="materials.current_page < materials.last_page"
                        :href="pageUrl(materials.current_page + 1)"
                        class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-800"
                    >
                        Вперёд →
                    </Link>
                    <span v-else class="text-gray-300">Вперёд →</span>
                </div>
            </div>

            <div v-else-if="search" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <p>По запросу «{{ search }}» ничего не найдено.</p>
            </div>

            <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <p>Введите поисковый запрос.</p>
            </div>
        </div>
    </LayoutSwitcher>
</template>

<script setup>
import {Link, Head, usePage} from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import Breadcrumbs from '@/components/shared/Breadcrumbs.vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    materials: {
        type: Object,
        default: () => ({data: []}),
    },
    search: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: 'Поиск',
    },
    description: {
        type: String,
        default: '',
    },
    keywords: {
        type: String,
        default: '',
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

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

const excerpt = (content) => {
    if (!content) return '';
    const text = content.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
    return text.length > 220 ? text.slice(0, 220) + '…' : text;
};

// Пагинатор не сохраняет query-string — добавляем q вручную,
// чтобы при переходе по страницам поиск не сбрасывался.
const pageUrl = (pageNumber) => {
    const base = props.materials.path || '/search';
    const query = props.search ? `?q=${encodeURIComponent(props.search)}&page=${pageNumber}` : `?page=${pageNumber}`;
    return base + query;
};
</script>
