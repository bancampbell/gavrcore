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
                <div class="prose max-w-none">
                    <template v-for="(part, index) in contentParts" :key="index">
                        <span v-if="part.type === 'html'" v-html="part.content"></span>
                        <GalleryRenderer v-else-if="part.type === 'gallery'" :gallery="part.data" />
                    </template>
                </div>

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
import { ref, onMounted } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';
import AppLayout from '@/layouts/AppLayout.vue';

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

const contentParts = ref([]);

const parseContent = async () => {
    const content = props.material.content || '';
    const regex = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g;

    const parts = [];
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(content)) !== null) {
        // HTML до шорткода
        if (match.index > lastIndex) {
            parts.push({
                type: 'html',
                content: content.substring(lastIndex, match.index)
            });
        }

        // Загружаем галерею
        const galleryId = match[1];
        try {
            const response = await axios.get(`/api/galleries/${galleryId}`);
            parts.push({
                type: 'gallery',
                data: response.data
            });
        } catch (error) {
            console.error(`Gallery ${galleryId} not found`);
            parts.push({
                type: 'html',
                content: `[gallery id="${galleryId}"]`
            });
        }

        lastIndex = regex.lastIndex;
    }

    // Остаток HTML после последнего шорткода
    if (lastIndex < content.length) {
        parts.push({
            type: 'html',
            content: content.substring(lastIndex)
        });
    }

    contentParts.value = parts;
};

onMounted(() => {
    parseContent();
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
