<template>
    <AppLayout>
        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description || siteDescription" />
            <meta name="keywords" :content="keywords || siteKeywords" />
        </Head>

        <div class="space-y-6">
            <!-- Hero -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow p-8 text-white">
                <h1 class="text-3xl font-bold">Добро пожаловать в GavrCore CMS</h1>
                <p class="mt-2 opacity-90">{{ siteDescription || 'Управляйте своим контентом легко и быстро.' }}</p>
            </div>

            <!-- Главный материал -->
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

                    <div class="prose max-w-none" v-html="homepageMaterial.content"></div>

                    <div v-if="showAuthor || showViews" class="mt-6 flex items-center justify-between text-sm text-gray-500">
                        <div class="flex items-center space-x-4">
                            <span v-if="showAuthor">Автор: {{ homepageMaterial.user?.name }}</span>
                            <span v-if="showViews">Просмотров: {{ homepageMaterial.views }}</span>
                        </div>
                        <Link :href="`/material/${homepageMaterial.alias}`" class="text-indigo-600 hover:text-indigo-800">
                            Читать полностью →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Если нет материала на главной -->
            <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <p>Нет материала, отмеченного для показа на главной.</p>
                <p class="text-sm mt-2">Выберите материал в админке и отметьте "Показывать на главной".</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    homepageMaterial: Object,
    title: String,
    description: String,
    keywords: String,
});

const siteDescription = appSettings.site_description || '';
const siteKeywords = appSettings.seo_keywords || '';

// Логика отображения для главной страницы
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

<style scoped>
.prose {
    max-width: none;
}

.prose img {
    max-width: 100%;
    height: auto;
}
</style>
