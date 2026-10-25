<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="material-selector-modal">
            <div class="material-selector-modal-header">
                <h3>Выберите материал</h3>
                <button @click="close" class="material-selector-modal-close">×</button>
            </div>
            <div class="material-selector-modal-body">
                <div class="filters-bar">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Поиск</label>
                        <input type="text" v-model="filters.search" placeholder="Введите название..." class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>
                    <div class="w-32">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Состояние</label>
                        <select v-model="filters.state" class="w-full border border-gray-300 rounded-md px-3 py-1.5 text-sm">
                            <option value="">Все</option>
                            <option value="published">Опубликовано</option>
                            <option value="draft">Не опубликовано</option>
                        </select>
                    </div>
                    <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">Очистить</button>
                </div>
                <div class="material-list-wrapper">
                    <div v-if="loading" class="text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        <div class="mt-2 text-gray-500">Загрузка...</div>
                    </div>
                    <div v-else>
                        <div class="flex bg-gray-50 border-b border-gray-200 py-2 text-sm font-medium text-gray-500">
                            <div class="w-16 px-4 text-center font-bold text-[#3071a9]">ID</div>
                            <div class="flex-1 px-4 font-bold text-[#3071a9]">Заголовок</div>
                            <div class="w-28 px-4 text-center font-bold text-[#3071a9]">Статус</div>
                        </div>
                        <div v-for="material in materialList" :key="material.id" @click="selectMaterial(material)" class="flex px-4 py-3 text-sm hover:bg-gray-50 border-b border-gray-100 cursor-pointer" :class="{ 'bg-indigo-50': selectedId === material.id }">
                            <div class="w-16 text-center text-gray-600">{{ material.id }}</div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ material.title }}</div>
                                <div class="text-xs text-gray-400">Слаг: {{ material.slug }}</div>
                            </div>
                            <div class="w-28 text-center">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap" :class="{ 'bg-green-100 text-green-800': material.state === 'published', 'bg-rose-100 text-rose-700': material.state === 'draft' }">{{ material.state === 'published' ? 'Опубликовано' : 'Не опубликовано' }}</span>
                            </div>
                        </div>
                        <div v-if="materialList.length === 0 && !loading" class="text-center py-8 text-gray-500">Материалы не найдены</div>
                    </div>
                </div>
            </div>
            <div class="material-selector-modal-footer">
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
const emit = defineEmits<{ (e: 'close'): void; (e: 'select', material: { id: number; title: string; slug: string }): void }>();

const loading = ref(false);
const filters = ref({ search: '', state: '' });
const allMaterials = ref<any[]>([]);
const selectedId = ref<number | null>(null);

// /admin/materials/list отдаёт голый массив (до 1000 записей), поэтому
// фильтрация поиском и состоянием выполняется на клиенте без повторных запросов.
const materialList = computed(() => {
    const search = filters.value.search.trim().toLowerCase();
    return allMaterials.value.filter((material) => {
        if (filters.value.state && material.state !== filters.value.state) return false;
        if (search) {
            const title = String(material.title ?? '').toLowerCase();
            const slug = String(material.slug ?? '').toLowerCase();
            if (!title.includes(search) && !slug.includes(search)) return false;
        }
        return true;
    });
});

const loadMaterials = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/materials/list');
        allMaterials.value = Array.isArray(response.data) ? response.data : [];
    } catch (error) {
        console.error('Error loading materials:', error);
        allMaterials.value = [];
    } finally {
        loading.value = false;
    }
};

const resetFilters = () => { filters.value = { search: '', state: '' }; };
const selectMaterial = (material: any) => { selectedId.value = material.id; };
const confirmSelect = () => {
    const material = allMaterials.value.find(m => m.id === selectedId.value);
    if (material) { emit('select', { id: material.id, title: material.title, slug: material.slug }); close(); }
};
const close = () => { selectedId.value = null; emit('close'); };

watch(() => props.show, async (val) => {
    if (val) {
        filters.value = { search: '', state: '' }; selectedId.value = null;
        await loadMaterials();
        if (props.selectedSlug) {
            const found = allMaterials.value.find(m => m.slug === props.selectedSlug);
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
.material-selector-modal { background: white; border-radius: 0.75rem; width: 900px; max-width: 90vw; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.material-selector-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0.75rem 0.75rem 0 0; flex-shrink: 0; }
.material-selector-modal-header h3 { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; }
.material-selector-modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1; padding: 0; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 0.375rem; transition: all 0.2s; }
.material-selector-modal-close:hover { background: #e5e7eb; color: #1f2937; }
.material-selector-modal-body { flex: 1; overflow-y: auto; padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.filters-bar { display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; padding-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.material-list-wrapper { flex: 1; overflow-y: auto; min-height: 300px; max-height: 400px; }
.material-selector-modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 0.75rem 0.75rem; flex-shrink: 0; }
.btn-cancel { padding: 0.5rem 1rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.btn-cancel:hover { background: #f3f4f6; }
.btn-primary { padding: 0.5rem 1rem; background: #4f46e5; color: white; border-radius: 0.375rem; border: none; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { background: #4338ca; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
</style>