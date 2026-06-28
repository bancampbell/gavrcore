<template>
    <AppLayout>
        <Head>
            <title>{{ title }}</title>
            <meta name="description" :content="description || siteDescription" />
            <meta name="keywords" :content="keywords || siteKeywords" />
        </Head>

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

                    <div class="prose max-w-none" ref="contentRef">
                        <template v-for="(part, index) in contentParts" :key="index">
                            <span v-if="part.type === 'html'" v-html="part.content"></span>
                            <GalleryRenderer v-else-if="part.type === 'gallery'" :gallery="part.data" />
                        </template>
                    </div>

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

            <div v-else class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
                <p>Нет материала, отмеченного для показа на главной.</p>
                <p class="text-sm mt-2">Выберите материал в админке и отметьте "Показывать на главной".</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link, Head, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import GalleryRenderer from '@/components/Gallery/GalleryRenderer.vue';
import AppLayout from '@/layouts/AppLayout.vue';

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

const contentParts = ref([]);
const contentRef = ref(null);

const parseContent = async () => {
    const content = props.homepageMaterial?.content || '';
    const regex = /\[gallery\s+id="(\d+)"(?:\s+name="([^"]*)")?\]/g;

    const parts = [];
    let lastIndex = 0;
    let match;

    while ((match = regex.exec(content)) !== null) {
        if (match.index > lastIndex) {
            parts.push({
                type: 'html',
                content: content.substring(lastIndex, match.index)
            });
        }

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

    if (lastIndex < content.length) {
        parts.push({
            type: 'html',
            content: content.substring(lastIndex)
        });
    }

    contentParts.value = parts;

    await nextTick();
    setupImageLightbox();
    setupImageLinks();
};

const setupImageLightbox = () => {
    if (!contentRef.value) return;

    const images = contentRef.value.querySelectorAll('img[data-lightbox="true"]:not(.gallery-renderer img)');
    images.forEach((img) => {
        img.removeEventListener('click', handleImageClick);
        img.addEventListener('click', handleImageClick);
        img.style.cursor = 'pointer';
    });
};

const handleImageClick = (event) => {
    const img = event.currentTarget;
    if (img.closest('.gallery-renderer')) return;

    if (window.__globalLightbox) {
        window.__globalLightbox.open([
            { src: img.src, alt: img.alt || 'Изображение' }
        ], 0);
    }
};

const setupImageLinks = () => {
    if (!contentRef.value) return;

    const links = contentRef.value.querySelectorAll('a[href$=".jpg"], a[href$=".jpeg"], a[href$=".png"], a[href$=".gif"], a[href$=".webp"], a[href$=".svg"]');
    links.forEach((link) => {
        link.removeEventListener('click', handleLinkClick);
        link.addEventListener('click', handleLinkClick);
        link.style.cursor = 'pointer';
    });
};

const handleLinkClick = (event) => {
    event.preventDefault();
    const link = event.currentTarget;
    const url = link.getAttribute('href');

    if (url && window.__globalLightbox) {
        window.__globalLightbox.open([
            { src: url, alt: link.textContent || 'Изображение' }
        ], 0);
    }
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
    cursor: pointer;
}

.prose a[href$=".jpg"],
.prose a[href$=".jpeg"],
.prose a[href$=".png"],
.prose a[href$=".gif"],
.prose a[href$=".webp"],
.prose a[href$=".svg"] {
    cursor: pointer;
}
</style>
