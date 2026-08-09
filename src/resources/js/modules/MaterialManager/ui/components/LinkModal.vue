<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="modal-content link-modal">
            <div class="modal-header">
                <h3 class="modal-title">Вставка ссылки</h3>
                <button @click="close" class="modal-close">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-row">
                    <label class="admin-form-label">Адрес</label>
                    <div class="input-group">
                        <input v-model="linkUrl" type="text" class="admin-form-input" placeholder="https://..." />
                        <button @click="openFileManager" type="button" class="btn-icon" title="Файловый менеджер">📄</button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Текст ссылки</label>
                    <input v-model="linkText" type="text" class="admin-form-input" placeholder="Текст ссылки" />
                </div>

                <div class="form-group">
                    <label class="admin-form-label">Контент</label>
                    <div class="category-tree-container">
                        <div class="search-box">
                            <input v-model="searchTerm" type="text" placeholder="Поиск..." class="search-input" @keyup.enter="searchMaterials" />
                            <button @click="searchMaterials" class="search-button">Поиск</button>
                        </div>
                        <div class="search-results">
                            <div v-if="searchTerm.trim()">
                                <div v-for="material in searchResults" :key="material.id" @click="selectMaterial(material)" class="material-item" :class="{ selected: selectedMaterialId === material.id }">
                                    {{ material.title }}
                                </div>
                                <div v-if="searchResults.length === 0" class="empty-message">Материалы не найдены</div>
                            </div>
                            <div v-else>
                                <ContentTree
                                    :categories="categories"
                                    :materials="materials"
                                    :selected-item-id="selectedMaterialId"
                                    :selected-type="selectedType"
                                    :expanded-categories="expandedCategories"
                                    @select-item="onSelectContentItem"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Цель</label>
                    <select v-model="linkTarget" class="admin-form-select">
                        <option value="_self">_self (текущее окно)</option>
                        <option value="_blank">_blank (новое окно)</option>
                    </select>
                </div>

                <div class="form-row">
                    <label class="admin-form-label">Название</label>
                    <input v-model="linkTitle" type="text" class="admin-form-input" placeholder="Всплывающая подсказка" />
                </div>
            </div>

            <div class="modal-footer">
                <button @click="close" class="btn-cancel">Отмена</button>
                <button @click="insertLink" class="btn-primary">Вставить ссылку</button>
            </div>
        </div>
    </div>

    <MediaManagerModal
        :show="showMediaManager"
        :user="user"
        :selected-url="linkUrl"
        mode="file"
        @close="showMediaManager = false"
        @select="onMediaSelect"
    />
</template>

<script setup lang="ts">
import { ref, watch, inject } from 'vue';
import ContentTree from './ContentTree.vue';
import MediaManagerModal from './MediaManagerModal.vue';

const props = defineProps<{
    show: boolean;
    categories?: any[];
    materials?: any[];
    editData?: {
        oldText: string;
        url: string;
        text: string;
        target: string;
        title: string;
    } | null;
    selectedText?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insert', data: { url: string; text: string; target: string; title: string }): void;
    (e: 'edit', data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }): void;
}>();

const user = inject('user') as any;

const linkUrl = ref('');
const linkText = ref('');
const linkTarget = ref('_self');
const linkTitle = ref('');
const searchTerm = ref('');
const searchResults = ref<any[]>([]);
const selectedMaterialId = ref<number | null>(null);
const selectedType = ref<string | null>(null);
const isEditMode = ref(false);
const originalLinkText = ref('');
const originalLinkUrl = ref('');
const expandedCategories = ref<number[]>([]);
const showMediaManager = ref(false);

const getMaterialUrl = (material: any): string => {
    if (material.slug) return `/${material.slug}`;
    if (material.alias) return `/${material.alias}`;
    return `/materials/${material.id}`;
};

const expandToMaterial = (url: string) => {
    if (!url || !props.materials) return;
    const cleanUrl = url.startsWith('/') ? url.substring(1) : url;
    const material = props.materials.find((m: any) => {
        if (m.slug && (cleanUrl === m.slug || url === `/${m.slug}`)) return true;
        if (m.alias && (cleanUrl === m.alias || url === `/${m.alias}`)) return true;
        if (url === `/materials/${m.id}`) return true;
        return false;
    });
    if (material) {
        selectedMaterialId.value = material.id;
        selectedType.value = 'material';
        const expandCategory = (categoryId: number | null) => {
            if (!categoryId) return;
            const category = props.categories?.find((c: any) => c.id === categoryId);
            if (category) {
                if (!expandedCategories.value.includes(category.id)) expandedCategories.value.push(category.id);
                if (category.parent_id) expandCategory(category.parent_id);
            }
        };
        expandCategory(material.category_id);
    }
};

watch(() => props.show, (val) => {
    if (val) {
        expandedCategories.value = [];
        if (props.editData) {
            isEditMode.value = true;
            linkUrl.value = props.editData.url;
            linkText.value = props.editData.text;
            linkTarget.value = props.editData.target === '_blank' ? '_blank' : '_self';
            linkTitle.value = props.editData.title;
            originalLinkText.value = props.editData.oldText;
            originalLinkUrl.value = props.editData.url;
            expandToMaterial(props.editData.url);
        } else {
            isEditMode.value = false;
            linkUrl.value = '';
            linkText.value = props.selectedText || '';
            linkTarget.value = '_self';
            linkTitle.value = '';
            originalLinkText.value = '';
            originalLinkUrl.value = '';
            selectedMaterialId.value = null;
            selectedType.value = null;
        }
        searchTerm.value = '';
        searchResults.value = [];
    }
});

const close = () => { emit('close'); };
const openFileManager = () => { showMediaManager.value = true; };

const onMediaSelect = (file: { url: string; name: string; path: string; options?: any }) => {
    linkUrl.value = file.url;
    if (!linkText.value) linkText.value = file.name;
    selectedMaterialId.value = null;
    selectedType.value = null;
    showMediaManager.value = false;
};

const searchMaterials = () => {
    const term = searchTerm.value.trim().toLowerCase();
    if (!term) {
        searchResults.value = [];
        return;
    }
    searchResults.value = (props.materials || []).filter((m: any) =>
        m.title?.toLowerCase().includes(term) ||
        m.slug?.toLowerCase().includes(term) ||
        m.alias?.toLowerCase().includes(term)
    );
};

const selectMaterial = (material: any) => {
    selectedMaterialId.value = material.id;
    selectedType.value = 'material';
    linkUrl.value = getMaterialUrl(material);
    const expandCategory = (categoryId: number | null) => {
        if (!categoryId) return;
        const category = props.categories?.find((c: any) => c.id === categoryId);
        if (category) {
            if (!expandedCategories.value.includes(category.id)) expandedCategories.value.push(category.id);
            if (category.parent_id) expandCategory(category.parent_id);
        }
    };
    expandCategory(material.category_id);
};

const onSelectContentItem = (item: any, type: 'category' | 'material') => {
    if (type === 'material') {
        selectedMaterialId.value = item.id;
        selectedType.value = 'material';
        linkUrl.value = getMaterialUrl(item);
    }
};

const insertLink = () => {
    if (!linkUrl.value) { alert('Введите адрес ссылки'); return; }
    const targetValue = linkTarget.value === '_blank' ? '_blank' : '_self';
    if (isEditMode.value) {
        emit('edit', { oldText: originalLinkText.value, newUrl: linkUrl.value, newText: linkText.value || linkUrl.value, newTarget: targetValue, newTitle: linkTitle.value });
        originalLinkText.value = linkText.value || linkUrl.value;
        originalLinkUrl.value = linkUrl.value;
    } else {
        emit('insert', { url: linkUrl.value, text: linkText.value || linkUrl.value, target: targetValue, title: linkTitle.value });
    }
    close();
};

const updateLink = insertLink;
</script>

<style scoped>
.modal-overlay { position: fixed; inset: 0; z-index: 9999; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; }
.modal-content.link-modal { background: white; border-radius: 0.5rem; width: 600px; max-width: 90%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); display: flex; flex-direction: column; max-height: 90vh; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: #ddd; border-bottom: 1px solid #ccc; flex-shrink: 0; }
.modal-title { font-size: 1rem; font-weight: 600; color: #1f2937; margin: 0; }
.modal-close { color: #4b5563; font-size: 1.75rem; font-weight: 700; background: none; border: none; cursor: pointer; line-height: 1; opacity: 0.7; }
.modal-close:hover { opacity: 1; }
.modal-body { padding: 1.5rem; flex: 1; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; background: #f9fafb; flex-shrink: 0; }
.form-row { display: flex; align-items: center; margin-bottom: 1rem; gap: 1rem; }
.form-row .admin-form-label { width: 120px; flex-shrink: 0; margin-bottom: 0; font-size: 0.8rem; font-weight: 500; color: #374151; }
.form-row .admin-form-input, .form-row .input-group, .form-row .admin-form-select { flex: 1; }
.input-group { display: flex; gap: 0.5rem; flex: 1; }
.admin-form-input { width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s; }
.admin-form-input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
.admin-form-select { width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; background: white; }
.admin-form-select:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
.btn-icon { padding: 0.5rem 0.75rem; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 0.375rem; cursor: pointer; }
.btn-icon:hover { background: #e5e7eb; }
.btn-cancel { padding: 0.5rem 1rem; background: white; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; }
.btn-cancel:hover { background: #f3f4f6; }
.btn-primary { padding: 0.5rem 1rem; background: #337ab7; color: white; border-radius: 0.375rem; border: none; font-size: 0.875rem; cursor: pointer; }
.btn-primary:hover { background: #286090; }
.form-group { margin-bottom: 1rem; }
.form-group .admin-form-label { display: block; margin-bottom: 0.5rem; font-size: 0.8rem; font-weight: 500; color: #374151; }
.category-tree-container { border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.75rem; max-height: 300px; overflow-y: auto; }
.search-box { display: flex; gap: 0.5rem; margin-bottom: 0.75rem; }
.search-input { flex: 1; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.875rem; }
.search-button { padding: 0.375rem 0.75rem; background: #337ab7; color: white; border: none; border-radius: 0.375rem; font-size: 0.875rem; cursor: pointer; }
.search-button:hover { background: #286090; }
.material-item { padding: 0.5rem; cursor: pointer; border-radius: 0.25rem; }
.material-item:hover { background-color: #f3f4f6; }
.material-item.selected { background-color: #e0f2fe; font-weight: 500; }
.empty-message { padding: 1rem; text-align: center; color: #9ca3af; font-size: 0.875rem; }
</style>
