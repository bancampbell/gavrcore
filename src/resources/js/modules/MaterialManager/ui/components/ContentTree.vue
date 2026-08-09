<template>
    <div class="content-tree">
        <div v-for="category in rootCategories" :key="category.id" class="tree-node">
            <div class="tree-node-header" @click="toggleCategory(category.id)">
                <span class="tree-toggle" :class="{ 'has-children': hasChildren(category.id) || getCategoryMaterialsCount(category.id) > 0 }">
                    <span v-if="isExpanded(category.id)" class="toggle-minus">−</span>
                    <span v-else-if="hasChildren(category.id) || getCategoryMaterialsCount(category.id) > 0" class="toggle-plus">+</span>
                    <span v-else class="toggle-placeholder"></span>
                </span>
                <svg class="tree-icon" fill="#D2B073" stroke="#D2B073" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <span class="tree-name">{{ category.name }}</span>
                <span class="tree-count">({{ getCategoryMaterialsCount(category.id) }})</span>
            </div>
            <div v-if="isExpanded(category.id)" class="tree-children">
                <div v-for="material in getCategoryMaterials(category.id)" :key="material.id" class="tree-item" @click="selectItem(material, 'material')" :class="{ selected: selectedItemId === material.id && selectedType === 'material' }">
                    <span class="tree-toggle-placeholder"></span>
                    <span class="tree-icon-text">📄</span>
                    <span class="tree-name">{{ material.title }}</span>
                </div>
                <ContentTree
                    v-for="child in getSafeChildCategories(category.id)"
                    :key="child.id"
                    :categories="categories"
                    :materials="materials"
                    :parent-id="child.id"
                    :level="level + 1"
                    :selected-item-id="selectedItemId"
                    :selected-type="selectedType"
                    :expanded-categories="expandedCategories"
                    :visited-ids="[...(visitedIds || []), category.id]"
                    @select-item="selectItem"
                    @update:expandedCategories="onChildExpandedUpdate"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import ContentTree from './ContentTree.vue';

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Material {
    id: number;
    title: string;
    category_id: number | null;
    slug?: string;
}

const props = defineProps<{
    categories: Category[];
    materials: Material[];
    parentId?: number | null;
    level?: number;
    selectedItemId?: number | null;
    selectedType?: string | null;
    expandedCategories?: number[];
    visitedIds?: number[];
}>();

const emit = defineEmits<{
    (e: 'select-item', item: any, type: 'category' | 'material'): void;
    (e: 'update:expandedCategories', value: number[]): void;
}>();

const level = props.level || 0;
const expandedCategories = ref<number[]>(props.expandedCategories || []);

watch(() => props.expandedCategories, (newVal) => {
    if (newVal) {
        expandedCategories.value = newVal;
    }
}, { deep: true });

const rootCategories = computed(() => {
    if (props.parentId !== undefined) {
        return props.categories.filter(c => c.parent_id === props.parentId);
    }
    return props.categories.filter(c => c.parent_id === null);
});

const getCategoryMaterials = (categoryId: number) => {
    return props.materials.filter(m => m.category_id === categoryId);
};

const getCategoryMaterialsCount = (categoryId: number) => {
    return getCategoryMaterials(categoryId).length;
};

const getChildCategories = (categoryId: number) => {
    return props.categories.filter(c => c.parent_id === categoryId);
};

const getSafeChildCategories = (categoryId: number): Category[] => {
    const visited = new Set(props.visitedIds || []);
    return getChildCategories(categoryId).filter(child => !visited.has(child.id));
};

const hasChildren = (categoryId: number) => {
    return getChildCategories(categoryId).length > 0;
};

const isExpanded = (categoryId: number) => {
    return expandedCategories.value.includes(categoryId);
};

const toggleCategory = (categoryId: number) => {
    const index = expandedCategories.value.indexOf(categoryId);
    if (index === -1) {
        expandedCategories.value.push(categoryId);
    } else {
        expandedCategories.value.splice(index, 1);
    }
    emit('update:expandedCategories', [...expandedCategories.value]);
};

const onChildExpandedUpdate = (value: number[]) => {
    const set = new Set([...expandedCategories.value, ...value]);
    expandedCategories.value = Array.from(set);
    emit('update:expandedCategories', [...expandedCategories.value]);
};

const selectItem = (item: any, type: 'category' | 'material') => {
    emit('select-item', item, type);
};
</script>

<style scoped>
.content-tree { font-size: 0.875rem; }
.tree-node { margin-left: 0; }
.tree-node-header { display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; cursor: pointer; border-radius: 0.25rem; }
.tree-node-header:hover { background-color: #f3f4f6; }
.tree-toggle { width: 1.5rem; height: 1.5rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.toggle-plus, .toggle-minus { width: 1.25rem; height: 1.25rem; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold; color: #4b5563; border: 1px solid #d1d5db; border-radius: 0.25rem; background: white; cursor: pointer; }
.toggle-plus:hover, .toggle-minus:hover { background-color: #f3f4f6; border-color: #9ca3af; }
.toggle-placeholder { width: 1.25rem; height: 1.25rem; }
.tree-toggle-placeholder { width: 1.5rem; flex-shrink: 0; }
.tree-item { display: flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.5rem; padding-left: 2rem; cursor: pointer; border-radius: 0.25rem; }
.tree-item:hover { background-color: #f3f4f6; }
.tree-item.selected { background-color: #e0f2fe; font-weight: 500; }
.tree-icon { width: 1rem; height: 1rem; flex-shrink: 0; }
.tree-icon-text { font-size: 0.875rem; width: 1.25rem; text-align: center; flex-shrink: 0; }
.tree-name { flex: 1; }
.tree-count { font-size: 0.7rem; color: #9ca3af; flex-shrink: 0; }
.tree-children { margin-left: 0.5rem; }
</style>
