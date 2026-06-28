<template>
    <AppLayout>
        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description || siteDescription" />
            <meta name="keywords" :content="keywords || siteKeywords" />
        </Head>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div v-if="showDate || showCategory" class="flex items-center justify-between mb-2">
                    <span v-if="showDate" class="text-xs text-gray-500">{{ formatDate(material.created_at) }}</span>
                    <span v-if="showCategory" class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                        {{ material.category?.name || 'Без категории' }}
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ material.title }}
                </h1>

                <!-- Контент с галереями -->
                <div class="prose max-w-none" ref="contentRef" v-html="renderedContent"></div>

                <div v-if="showAuthor || showViews" class="mt-6 flex items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span v-if="showAuthor">Автор: {{ material.user?.name }}</span>
                        <span v-if="showViews">Просмотров: {{ material.views }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {ref, onMounted, nextTick} from 'vue';
import {Head, usePage} from '@inertiajs/vue3';
import {useGalleryParser} from '@/composables/useGalleryParser';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';
import {createApp} from 'vue';

const page = usePage();
const appSettings = page.props.appSettings || {};

const props = defineProps({
    material: Object,
    title: String,
    description: String,
    keywords: String,
});

const siteDescription = appSettings.site_description || '';
const siteKeywords = appSettings.seo_keywords || '';

// Логика отображения
const useGlobal = props.material.use_global_settings ?? true;

const showDate = useGlobal
    ? (appSettings.show_date ?? true)
    : (props.material.show_date ?? true);

const showAuthor = useGlobal
    ? (appSettings.show_author ?? true)
    : (props.material.show_author ?? true);

const showCategory = useGlobal
    ? (appSettings.show_category ?? true)
    : (props.material.show_category ?? true);

const showViews = useGlobal
    ? (appSettings.show_views ?? true)
    : (props.material.show_views ?? true);

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('ru-RU');
};

// Рендер контента с галереями
const renderedContent = ref(props.material.content || '');
const contentRef = ref(null);

const renderGalleries = async () => {
    console.log('=== renderGalleries START ===');
    console.log('material.content:', props.material.content);

    const {parseGalleries} = useGalleryParser();
    const result = await parseGalleries(props.material.content || '');

    console.log('parseGalleries result.content:', result.content);
    console.log('parseGalleries result.galleries:', result.galleries);

    renderedContent.value = result.content;

    // После рендера контента монтируем компоненты галерей
    await nextTick();

    console.log('contentRef.value:', contentRef.value);
    console.log('renderedContent.value:', renderedContent.value);

    for (const [id, galleryData] of Object.entries(result.galleries)) {
        console.log(`Processing gallery ${id}:`, galleryData);
        if (galleryData) {
            const container = contentRef.value?.querySelector(`[data-gallery-id="${id}"]`);
            console.log(`container for gallery ${id}:`, container);
            if (container) {
                const mountDiv = document.createElement('div');
                container.replaceWith(mountDiv);

                createApp(GalleryRenderer, {
                    gallery: galleryData
                }).mount(mountDiv);

                console.log(`Gallery ${id} mounted successfully`);
            } else {
                console.log(`Container for gallery ${id} NOT FOUND`);
            }
        }
    }

    console.log('=== renderGalleries END ===');
};

onMounted(() => {
    renderGalleries();
});
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
