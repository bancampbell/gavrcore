<template>
    <div v-if="show" class="modal-overlay" @click.self="close">
        <div class="link-modal link-modal-fixed">
            <div class="link-modal-header">
                <h3>Ссылка</h3>
                <button @click="close" class="link-modal-close">×</button>
            </div>

            <div class="nav-tabs-wrapper">
                <div class="nav-tabs">
                    <button
                        @click="activeTab = 'link'"
                        :class="['nav-item', { active: activeTab === 'link' }]"
                    >
                        Ссылка
                    </button>
                    <button
                        @click="activeTab = 'advanced'"
                        :class="['nav-item', { active: activeTab === 'advanced' }]"
                    >
                        Расширенные
                    </button>
                    <button
                        @click="activeTab = 'popup'"
                        :class="['nav-item', { active: activeTab === 'popup' }]"
                    >
                        Всплывающие окна
                    </button>
                </div>
            </div>

            <div class="tab-content-fixed">
                <div v-show="activeTab === 'link'" class="tab-pane">
                    <div class="form-row">
                        <label class="form-label">Адрес</label>
                        <div class="input-group">
                            <input
                                v-model="linkUrl"
                                type="text"
                                class="form-input"
                                placeholder="https://..."
                            />
                            <button
                                @click="openFileManager"
                                type="button"
                                class="btn-icon"
                                title="Файловый менеджер"
                            >
                                📄
                            </button>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Текст ссылки</label>
                        <input
                            v-model="linkText"
                            type="text"
                            class="form-input"
                            placeholder="Текст ссылки"
                        />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Контент</label>
                        <div class="category-tree-container">
                            <div class="search-box">
                                <input
                                    v-model="searchTerm"
                                    type="text"
                                    placeholder="Поиск..."
                                    class="search-input"
                                />
                                <button @click="searchMaterials" class="search-button">
                                    Поиск
                                </button>
                            </div>
                            <div class="search-results">
                                <div v-if="searchTerm">
                                    <div
                                        v-for="material in searchResults"
                                        :key="material.id"
                                        @click="selectMaterial(material)"
                                        class="material-item"
                                        :class="{ selected: selectedMaterialId === material.id }"
                                    >
                                        {{ material.title }}
                                    </div>
                                    <div v-if="searchResults.length === 0" class="empty-message">
                                        Материалы не найдены
                                    </div>
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
                        <label class="form-label">Цель</label>
                        <select v-model="linkTarget" class="form-select">
                            <option value="">-- Не выбрано --</option>
                            <option value="_blank">_blank</option>
                            <option value="_self">_self</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Название</label>
                        <input
                            v-model="linkTitle"
                            type="text"
                            class="form-input"
                            placeholder="Всплывающая подсказка"
                        />
                    </div>
                </div>

                <div v-show="activeTab === 'advanced'" class="tab-pane tab-placeholder">
                    <div class="placeholder-content">
                        <p>Расширенные настройки будут добавлены позже</p>
                    </div>
                </div>

                <div v-show="activeTab === 'popup'" class="tab-pane tab-placeholder">
                    <div class="placeholder-content">
                        <p>Настройка всплывающих окон будет добавлена позже</p>
                    </div>
                </div>
            </div>

            <div class="link-modal-footer">
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
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'insert', data: { url: string; text: string; target: string; title: string }): void;
    (e: 'edit', data: { oldText: string; newUrl: string; newText: string; newTarget: string; newTitle: string }): void;
}>();

const user = inject('user') as any;

const activeTab = ref('link');
const linkUrl = ref('');
const linkText = ref('');
const linkTarget = ref('');
const linkTitle = ref('');
const searchTerm = ref('');
const searchResults = ref<any[]>([]);
const selectedMaterialId = ref<number | null>(null);
const selectedType = ref<string | null>(null);
const isEditMode = ref(false);
const originalLinkText = ref('');
const expandedCategories = ref<number[]>([]);
const showMediaManager = ref(false);

const expandToMaterial = (url: string) => {
    if (!url || !props.materials) return;

    const material = props.materials.find(m =>
        (m.slug && url.includes(m.slug)) ||
        url.includes(`/materials/${m.id}`)
    );

    if (material) {
        selectedMaterialId.value = material.id;
        selectedType.value = 'material';
        linkText.value = material.title;

        const expandCategory = (categoryId: number | null) => {
            if (!categoryId) return;
            const category = props.categories?.find(c => c.id === categoryId);
            if (category) {
                if (!expandedCategories.value.includes(category.id)) {
                    expandedCategories.value.push(category.id);
                }
                if (category.parent_id) {
                    expandCategory(category.parent_id);
                }
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
            linkTarget.value = props.editData.target;
            linkTitle.value = props.editData.title;
            originalLinkText.value = props.editData.oldText;
            expandToMaterial(props.editData.url);
        } else {
            isEditMode.value = false;
            linkUrl.value = '';
            linkText.value = '';
            linkTarget.value = '';
            linkTitle.value = '';
            originalLinkText.value = '';
        }
        searchTerm.value = '';
        activeTab.value = 'link';
    }
});

const close = () => {
    emit('close');
};

const openFileManager = () => {
    showMediaManager.value = true;
};

const onMediaSelect = (file: { url: string; name: string; path: string }) => {
    linkUrl.value = file.url;
    if (!linkText.value) {
        linkText.value = file.name;
    }
    showMediaManager.value = false;
};

const searchMaterials = () => {
    console.log('Search materials:', searchTerm.value);
};

const selectMaterial = (material: any) => {
    selectedMaterialId.value = material.id;
    selectedType.value = 'material';
    linkUrl.value = material.slug || `/materials/${material.id}`;
    if (!linkText.value) {
        linkText.value = material.title;
    }
};

const onSelectContentItem = (item: any, type: 'category' | 'material') => {
    if (type === 'material') {
        selectedMaterialId.value = item.id;
        selectedType.value = 'material';
        linkUrl.value = item.slug || `/materials/${item.id}`;
        if (!linkText.value) {
            linkText.value = item.title;
        }
    }
};

const insertLink = () => {
    if (!linkUrl.value) {
        alert('Введите адрес ссылки');
        return;
    }

    if (isEditMode.value) {
        emit('edit', {
            oldText: originalLinkText.value,
            newUrl: linkUrl.value,
            newText: linkText.value || linkUrl.value,
            newTarget: linkTarget.value,
            newTitle: linkTitle.value
        });
    } else {
        emit('insert', {
            url: linkUrl.value,
            text: linkText.value || linkUrl.value,
            target: linkTarget.value,
            title: linkTitle.value
        });
    }
    close();
};

const updateLink = insertLink;
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.link-modal-fixed {
    background: white;
    border-radius: 0.5rem;
    width: 700px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    height: 850px;
    max-height: 85vh;
}

.link-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #ddd;
    border-bottom: 1px solid #ccc;
    flex-shrink: 0;
}

.link-modal-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.link-modal-close {
    color: #4b5563;
    font-size: 1.75rem;
    font-weight: 700;
    background: none;
    border: none;
    cursor: pointer;
    line-height: 1;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.link-modal-close:hover {
    opacity: 1;
    color: #1f2937;
}

.nav-tabs-wrapper {
    padding: 0.75rem 1.5rem 0 1.5rem;
    background: white;
    flex-shrink: 0;
}

.nav-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid #dee2e6;
}

.nav-item {
    padding: 0.625rem 1rem;
    background: none;
    border: none;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    margin-bottom: -1px;
}

.nav-item:hover {
    color: #4f46e5;
}

.nav-item.active {
    color: #4f46e5;
    background: white;
    border: 1px solid #dee2e6;
    border-bottom-color: white;
    border-radius: 0.25rem 0.25rem 0 0;
}

.tab-content-fixed {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}

.tab-pane {
    width: 100%;
}

.form-row {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    gap: 1rem;
}

.form-row .form-label {
    width: 120px;
    flex-shrink: 0;
    margin-bottom: 0;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
}

.form-row .form-input,
.form-row .input-group,
.form-row .form-select {
    flex: 1;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group .form-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.category-tree-container {
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    background: #fafafa;
    overflow: hidden;
}

.search-box {
    display: flex;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
    background: #fafafa;
}

.search-input {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.search-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.search-button {
    padding: 0.5rem 1rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.search-button:hover {
    background: #e5e7eb;
}

.search-results {
    height: 200px;
    background: #fafafa;
    overflow-y: auto;
}

.material-item {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    font-size: 0.875rem;
}

.material-item:hover {
    background-color: #f3f4f6;
}

.material-item.selected {
    background-color: #e0f2fe;
    font-weight: 500;
}

.empty-message {
    padding: 0.5rem 0.75rem;
    color: #9ca3af;
    font-size: 0.875rem;
    text-align: center;
}

.form-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
}

.input-group {
    display: flex;
    gap: 0.5rem;
    flex: 1;
}

.btn-icon {
    padding: 0.5rem 0.75rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    cursor: pointer;
}

.btn-icon:hover {
    background: #e5e7eb;
}

.form-select {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    background: white;
}

.link-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    flex-shrink: 0;
}

.btn-cancel {
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-cancel:hover {
    background: #f3f4f6;
}

.btn-primary {
    padding: 0.5rem 1rem;
    background: #337ab7;
    color: white;
    border-radius: 0.375rem;
    border: none;
    font-size: 0.875rem;
    cursor: pointer;
}

.btn-primary:hover {
    background: #286090;
}

.tab-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
}

.placeholder-content {
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}
</style>
