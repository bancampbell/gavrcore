<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="close"
        >
            <div class="bg-white rounded-lg shadow-xl w-[800px] max-h-[80vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800">Вставить галерею</h2>
                    <button
                        @click="close"
                        class="text-gray-400 hover:text-gray-600 transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-auto p-4">
                    <!-- Search -->
                    <div class="mb-4">
                        <input
                            v-model="searchQuery"
                            type="text"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Поиск галерей..."
                        />
                    </div>

                    <!-- Loading -->
                    <div v-if="loading" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>

                    <!-- Gallery list -->
                    <div v-else-if="filteredGalleries.length === 0" class="text-center py-8 text-gray-500">
                        <p>Нет доступных галерей</p>
                        <p class="text-sm mt-1">Создайте галерею в разделе "Галереи" админки</p>
                    </div>

                    <div v-else class="grid grid-cols-2 gap-3">
                        <div
                            v-for="gallery in filteredGalleries"
                            :key="gallery.id"
                            class="border border-gray-200 rounded-lg p-3 hover:border-blue-400 hover:shadow-md transition cursor-pointer"
                            @click="selectGallery(gallery)"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800">{{ gallery.title }}</h3>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded">
                                            {{ gallery.type }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ gallery.images_count || 0 }} изображений
                                        </span>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-400">
                                        ID: {{ gallery.id }}
                                    </div>
                                </div>
                                <div class="ml-2">
                                    <button
                                        @click.stop="selectGallery(gallery)"
                                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                    >
                                        Вставить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
                    <button
                        @click="close"
                        class="px-4 py-2 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
                    >
                        Отмена
                    </button>
                    <Link
                        href="/admin/galleries"
                        class="px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 transition"
                    >
                        + Создать галерею
                    </Link>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'select', galleryId: number, galleryName: string): void;
}>();

const loading = ref(false);
const galleries = ref<any[]>([]);
const searchQuery = ref('');

const filteredGalleries = computed(() => {
    if (!searchQuery.value) return galleries.value;
    const q = searchQuery.value.toLowerCase();
    return galleries.value.filter(g =>
        g.title.toLowerCase().includes(q) ||
        g.id.toString().includes(q)
    );
});

const loadGalleries = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/galleries/list');
        galleries.value = response.data;
    } catch (error) {
        console.error('Error loading galleries:', error);
    } finally {
        loading.value = false;
    }
};

const selectGallery = (gallery: any) => {
    emit('select', gallery.id, gallery.title);
    emit('close');
};

const close = () => {
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        loadGalleries();
    }
});
</script>
