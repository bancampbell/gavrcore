<template>
    <LayoutSwitcher>
        <SeoHead :title="category?.name || 'Категория'" :description="category?.description || ''" :meta="meta" />

        <div class="container mx-auto px-4">
            <Breadcrumbs :items="breadcrumbs || []" />

            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-100">
                    <h1 class="text-3xl font-bold text-gray-900">{{ category?.name }}</h1>
                    <p v-if="category?.description" class="mt-2 text-gray-600">{{ category.description }}</p>
                </div>

                <template v-if="childrenList.length || materialsList.length">
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="child in childrenList"
                            :key="'cat-' + child.id"
                            class="px-6 py-3 hover:bg-blue-50 transition-colors"
                        >
                            <Link :href="`/category/${child.alias}`" class="font-medium text-gray-900 hover:text-indigo-600">
                                {{ child.name }}
                            </Link>
                        </div>
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
import {Link} from '@inertiajs/vue3';
import LayoutSwitcher from '@/layouts/LayoutSwitcher.vue';
import SeoHead from '@/components/shared/SeoHead.vue';
import Breadcrumbs from '../components/Breadcrumbs.vue';

const props = defineProps({
    category: {
        type: Object,
        default: () => ({}),
    },
    materials: {
        type: Object,
        default: () => ({data: []}),
    },
    children: {
        type: Array,
        default: () => [],
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

const materialsList = props.materials?.data || [];
const childrenList = props.children || [];

const materialUrl = (material) => {
    const slug = material.slug || material.alias;
    return material.category ? `/${material.category.alias}/${slug}` : `/${slug}`;
};
</script>
