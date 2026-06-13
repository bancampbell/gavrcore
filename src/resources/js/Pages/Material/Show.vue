<template>
    <AppLayout>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">{{ formatDate(material.created_at) }}</span>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                        {{ material.category?.name || 'Без категории' }}
                    </span>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    {{ material.title }}
                </h1>

                <div class="prose max-w-none" v-html="material.content"></div>

                <div class="mt-6 flex items-center justify-between text-sm text-gray-500">
                    <div class="flex items-center space-x-4">
                        <span>Автор: {{ material.user?.name }}</span>
                        <span>Просмотров: {{ material.views }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
    material: Object,
});

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
