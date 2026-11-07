<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="category-selector-modal">
            <div class="category-selector-modal-header">
                <h3>Выберите категорию</h3>
                <button @click="close" class="category-selector-modal-close">×</button>
            </div>
            <div class="category-selector-modal-body">
                <div class="filters-bar">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Поиск</label>
                        <input type="text" v-model="filters.search" placeholder="Введите название..." class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">Очистить</button>
                </div>
                <div class="category-list-wrapper">
                    <div v-if="loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <div class="mt-2 text-gray-500">Загрузка...</div>
                    </div>
                    <div v-else>
                        <div class="flex bg-gray-50 border-b border-gray-200 py-2 text-sm font-medium text-gray-500">
                            <div class="w-16 px-4 text-center font-bold text-[#3071a9]">ID</div>
                            <div class="flex-1 px-4 font-bold text-[#3071a9]">Название</div>
                        </div>
                        <div v-for="category in categoryList" :key="category.id" @click="selectCategory(category)" class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100 cursor-pointer" :class="{ 'bg-indigo-50': selectedId === category.id }">
                            <div class="w-16 text-center text-gray-600">{{ category.id }}</div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ '— '.repeat(category.depth || 0) }}{{ category.name }}</div>
                                <div class="text-xs text-gray-400">Слаг: {{ category.slug }}</div>
                            </div>
                        </div>
                        <div v-if="categoryList.length === 0 && !loading" class="text-center py-8 text-gray-500">Категории не найдены</div>
                    </div>
                </div>
            </div>
            <div class="category-selector-modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="confirmSelect" class="btn-primary" :disabled="!selectedId">Выбрать</button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps<{ show: boolean; selectedSlug?: string }>();
const emit = defineEmits<{ (e: 'close'): void; (e: 'select', category: { id: number; name: string; slug: string }): void }>();

const loading = ref(false);
const filters = ref({ search: '' });
const flatCategories = ref<any[]>([]);
const selectedId = ref<number | null>(null);

// /admin/categories/tree отдаёт вложенное дерево (поле slug добавлено для MenuManager),
// поэтому перед фильтрацией оно разворачивается в плоский список с отступами по depth.
const categoryList = computed(() => {
    const search = filters.value.search.trim().toLowerCase();
    return flatCategories.value.filter((category) => {
        if (!search) return true;
        const name = String(category.name ?? '').toLowerCase();
        const slug = String(category.slug ?? '').toLowerCase();
        return name.includes(search) || slug.includes(search);
    });
});

const flattenTree = (items: any[], levelOffset = 0): any[] => {
    let result: any[] = [];
    for (const item of items) {
        result.push({ ...item, depth: (item.depth ?? 0) + levelOffset });
        if (item.children?.length) result = [...result, ...flattenTree(item.children, levelOffset)];
    }
    return result;
};

const loadCategories = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/categories/tree');
        const tree = Array.isArray(response.data) ? response.data : (response.data?.data ?? []);
        flatCategories.value = flattenTree(tree);
    } catch (error) {
        console.error('Error loading categories:', error);
        flatCategories.value = [];
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => { filters.value = { search: '' }; };
const selectCategory = (category: any) => { selectedId.value = category.id; };
const confirmSelect = () => {
    const category = flatCategories.value.find(c => c.id === selectedId.value);
    if (category) { emit('select', { id: category.id, name: category.name, slug: category.slug }); close(); }
};
const close = () => { selectedId.value = null; emit('close'); };

watch(() => props.show, async (val) => {
    if (val) {
        filters.value = { search: '' }; selectedId.value = null;
        await loadCategories();
        if (props.selectedSlug) {
            const found = flatCategories.value.find(c => c.slug === props.selectedSlug);
            if (found) {
                selectedId.value = found.id;
                await nextTick();
                const selectedRow = document.querySelector('.bg-indigo-50');
                if (selectedRow) selectedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }
});
</script>

<style scoped>
.modal-overlay { position: fixed; inset: 0; z-index: 10000; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; }
.category-selector-modal { background: white; border-radius: 0.75rem; width: 900px; max-width: 90vw; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.category-selector-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0.75rem 0.75rem 0 0; flex-shrink: 0; }
.category-selector-modal-header h3 { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; }
.category-selector-modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 0.375rem; transition: all 0.2s; }
.category-selector-modal-close:hover { background: #e5e7eb; color: #1f2937; }
.category-selector-modal-body { flex: 1; overflow-y: auto; padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.filters-bar { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.category-list-wrapper { flex: 1; overflow-y: auto; min-height: 300px; max-height: 400px; }
.category-selector-modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 0.75rem 0.75rem; flex-shrink: 0; }
.btn-cancel { padding: 0.5rem 1rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #f3f4f6; }
.btn-primary { padding: 0.5rem 1rem; background: #4f46e5; color: white; border-radius: 0.375rem; border: none; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { background: #4338ca; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
